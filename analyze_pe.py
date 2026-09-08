# -*- coding: utf-8 -*-
"""Reusable PE static analysis: headers, sections/entropy, imports, overlay, entry."""
import sys, math, datetime
import pefile
from capstone import Cs, CS_ARCH_X86, CS_MODE_32, CS_MODE_64

PATH = sys.argv[1]
data = open(PATH, "rb").read()
pe = pefile.PE(data=data)

m = pe.FILE_HEADER.Machine
arch = {0x14c: "x86 (32-bit)", 0x8664: "x64 (64-bit)", 0xaa64: "ARM64"}.get(m, hex(m))
ts = datetime.datetime.fromtimestamp(pe.FILE_HEADER.TimeDateStamp, datetime.timezone.utc)
print("=== BASIC ===")
print(f"Arch        : {arch}")
print(f"Timestamp   : {ts} UTC")
print(f"Subsystem   : {pe.OPTIONAL_HEADER.Subsystem} (2=GUI)")
print(f"Sections    : {pe.FILE_HEADER.NumberOfSections}")

print("\n=== SECTIONS / ENTROPY ===")
for s in pe.sections:
    name = s.Name.rstrip(b"\x00").decode(errors="replace")
    raw = data[s.PointerToRawData : s.PointerToRawData + s.SizeOfRawData]
    if raw:
        freq = [0] * 256
        for b in raw:
            freq[b] += 1
        ent = -sum((c / len(raw)) * math.log2(c / len(raw)) for c in freq if c)
    else:
        ent = 0.0
    flags = "".join(n for bit, n in ((0x20000000,"X"),(0x40000000,"R"),(0x80000000,"W")) if s.Characteristics & bit)
    print(f"{name:<10} vsize={s.Misc_VirtualSize:#10x} rawsize={s.SizeOfRawData:#10x} entropy={ent:.2f} perms={flags}")

# Overlay = appended data after last section (installers put payload here)
last = max(s.PointerToRawData + s.SizeOfRawData for s in pe.sections)
overlay = len(data) - last
print(f"\n=== OVERLAY (appended payload) ===")
print(f"appended bytes: {overlay:,} ({overlay/1024/1024:.1f} MB)")
if overlay > 0:
    print(f"overlay magic : {data[last:last+8].hex()} ({data[last:last+8]!r})")

print("\n=== IMPORT DLLs ===")
for entry in getattr(pe, "DIRECTORY_ENTRY_IMPORT", []):
    print(f"  {entry.dll.decode()} ({len(entry.imports)} apis)")

print("\n=== DELAY-IMPORT DLLs ===")
for entry in getattr(pe, "DIRECTORY_ENTRY_DELAY_IMPORT", []):
    print(f"  {entry.dll.decode()} ({len(entry.imports)} apis)")

print("\n=== TLS CALLBACKS ===")
tls = getattr(pe, "DIRECTORY_ENTRY_TLS", None)
if tls and tls.struct.AddressOfCallBacks:
    import struct
    cb = tls.struct.AddressOfCallBacks
    off = pe.get_offset_from_rva(cb - pe.OPTIONAL_HEADER.ImageBase)
    i = 0
    while True:
        ptr = struct.unpack("<I", data[off+i*4:off+i*4+4])[0] if arch.startswith("x86") else struct.unpack("<Q", data[off+i*8:off+i*8+8])[0]
        if not ptr: break
        print(f"  callback[{i}] = 0x{ptr:X}")
        i += 1
    if i == 0: print("  (empty - normal)")
else:
    print("  none (normal)")

print("\n=== ENTRY POINT (first 40 instrs) ===")
ep_rva = pe.OPTIONAL_HEADER.AddressOfEntryPoint
ep_off = pe.get_offset_from_rva(ep_rva)
code = data[ep_off : ep_off + 300]
md = Cs(CS_ARCH_X86, CS_MODE_32 if arch.startswith("x86") else CS_MODE_64)
for i, ins in enumerate(md.disasm(code, pe.OPTIONAL_HEADER.ImageBase + ep_rva)):
    print(f"0x{ins.address:012X}  {ins.mnemonic:<8} {ins.op_str}")
    if i >= 40: break
