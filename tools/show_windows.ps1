Add-Type -TypeDefinition @'
using System;
using System.Runtime.InteropServices;
using System.Text;
using System.Collections.Generic;
public class WinList {
  private delegate bool EnumProc(IntPtr hWnd, IntPtr lParam);
  [DllImport("user32.dll")] private static extern bool EnumWindows(EnumProc cb, IntPtr lParam);
  [DllImport("user32.dll")] private static extern uint GetWindowThreadProcessId(IntPtr hWnd, out uint pid);
  [DllImport("user32.dll")] private static extern bool IsWindowVisible(IntPtr hWnd);
  [DllImport("user32.dll", CharSet=CharSet.Unicode)] private static extern int GetWindowText(IntPtr hWnd, StringBuilder sb, int max);
  [DllImport("user32.dll")] private static extern bool ShowWindow(IntPtr hWnd, int cmd);
  [DllImport("user32.dll")] private static extern bool SetForegroundWindow(IntPtr hWnd);
  [DllImport("user32.dll")] private static extern bool IsIconic(IntPtr hWnd);
  public static List<string> List = new List<string>();
  private static EnumProc cb = delegate(IntPtr h, IntPtr l) {
    uint pid; GetWindowThreadProcessId(h, out pid);
    if (pid == (uint)l.ToInt32()) {
      var sb = new StringBuilder(256);
      GetWindowText(h, sb, 256);
      string t = sb.ToString();
      List.Add("hwnd=" + h + " vis=" + IsWindowVisible(h) + " min=" + IsIconic(h) + " title='" + t + "'");
      if (t.Length > 0) { ShowWindow(h, 9); SetForegroundWindow(h); }
    }
    return true;
  };
  public static void Scan(uint pid) { List.Clear(); EnumWindows(cb, (IntPtr)pid); }
}
'@
foreach ($p in (Get-Process | Where-Object { $_.Name -like "*Qoder*" })) {
  [WinList]::Scan([uint32]$p.Id)
  if ([WinList]::List.Count -gt 0) { [WinList]::List }
}
