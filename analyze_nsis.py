# -*- coding: utf-8 -*-
"""Minimal NSIS parser: read firstheader, decompress header block, extract file manifest."""
import sys, re, zlib, bz2, lzma
import pefile

PATH = sys.argv[1]
data = open(PATH, "rb").read()
pe = pefile.PE(data=data)
overlay_off = max(s.PointerToRawData + s.SizeOfRawData for s in pe.sections)
ov = data[overlay_off:]

print("=== NSIS FIRSTHEADER ===")
import struct
flags, siginfo, len1, len2 = struct.unpack("<IIII", ov[:16])
print(f"flags        = 0x{flags:08X}")
print(f"siginfo      = 0x{siginfo:08X} {'(NSIS OK)' if siginfo == 0xDEADBEEF else '(MISMATCH!)'}")
print(f"len1(hdr)    = {len1:,}")
print(f"len2(total)  = {len2:,}")
print(f"overlay size = {len(ov):,}")
print(f"bytes 0..32  = {ov[:32].hex()}")

# NSIS firstheader is 0x1C bytes; compressed stream starts right after
comp = ov[0x1C:]
magic = comp[:3]
print(f"\nfirst compressed bytes: {comp[:8].hex()} ({comp[:4]!r})")

def try_zlib(c):
    return zlib.decompressobj().decompress(c, 50 * 1024 * 1024)

def try_bz2(c):
    return bz2.BZ2Decompressor().decompress(c, 50 * 1024 * 1024)

def try_lzma(c):
    # NSIS LZMA block = 5-byte props + 8-byte size + LZMA1 stream == lzma "alone" format
    d = lzma.LZMADecompressor(format=lzma.FORMAT_ALONE)
    return d.decompress(c, 50 * 1024 * 1024)

header = None
method = None
for name, fn in (("zlib/deflate", try_zlib), ("bzip2", try_bz2), ("lzma", try_lzma)):
    try:
        out = fn(comp)
        if out and len(out) > 100:
            header = out
            method = name
            break
    except Exception as e:
        print(f"  {name}: {type(e).__name__}: {str(e)[:80]}")

if header is None:
    print("\n!! could not decompress header block with any method")
    sys.exit(1)

print(f"\n=== DECOMPRESSED HEADER BLOCK: {method}, {len(header):,} bytes ===")

# NSIS header starts with: 4-byte size, then blocks: 0=cmds, 1=entries, 2=strings, ...
# entries block: 4-byte count, then 16-byte entries; string table has the names.
# Parse block sizes to locate the string table.
pos = 0
def rd32():
    global pos
    v = struct.unpack("<I", header[pos:pos+4])[0]
    pos += 4
    return v

total = rd32()
print(f"header size field: {total:,}")
blocks = {}
for b in range(4):  # cmds, entries, strings, language tables(0 or more - just read until >size)
    if pos >= len(header) or pos >= total:
        break
    bsize = rd32()
    blocks[b] = (pos, bsize)
    print(f"block {b}: offset={pos:,} size={bsize:,}")
    pos += bsize
    if b == 0 and bsize == 0xFFFFFFFF:  # uncompressed size marker variant
        break

# String table (block 2) contains null-separated strings (mixed codepages/lang tables)
if 2 in blocks:
    off, size = blocks[2]
    raw = header[off:off+size]
    # try utf-16le first (NSIS unicode), fallback to cp1252/ascii
    def strings_from(buf, enc, minlen=4):
        out = []
        cur = b""
        i = 0
        while i < len(buf):
            if enc == "u16":
                ch = buf[i:i+2]
                i += 2
                if ch in (b"\x00\x00", b""):
                    if len(cur) >= minlen * 2:
                        try: out.append(cur.decode("utf-16-le"))
                        except: pass
                    cur = b""
                else:
                    cur += ch
            else:
                c = buf[i:i+1]; i += 1
                if c == b"\x00":
                    if len(cur) >= minlen: out.append(cur.decode("cp936", "replace"))
                    cur = b""
                else:
                    cur += c
        return out

    u16 = strings_from(raw, "u16")
    asc = strings_from(raw, "a")
    cand = u16 if len(u16) > len(asc) else asc
    print(f"\nstrings decoded: {len(cand):,} (mode={'utf16' if len(u16)>len(asc) else 'cp936'})")
    # interesting names
    pats = re.compile(r"\.(exe|dll|sys|bat|cmd|ps1|vbs|js|scr|msi|dat|ini|xml|json|7z|cab|zip)$", re.I)
    files = [s for s in cand if pats.search(s) and 2 < len(s) < 120 and not s.startswith("NULLSOFT")]
    print(f"\n=== FILE-LIKE STRINGS (first 80) ===")
    for f in files[:80]:
        print(" ", f)
    # suspicious indicators
    print(f"\n=== SUSPICIOUS STRING SCAN ===")
    susp = [s for s in cand if re.search(r"(powershell|cmd\.exe|/c\s|http[s]?://|bitsadmin|certutil|schtasks|regsvr32|rundll32|vssadmin|bcdedit|wmic|\\AppData\\|Temp\\|Startup)", s, re.I)]
    for s in susp[:40]:
        print("  [!]", s)
    if not susp:
        print("  (none)")
