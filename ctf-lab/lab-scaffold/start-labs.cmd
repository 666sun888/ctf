@echo off
echo starting course lab servers (hidden background, no windows to close)...
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8090','-t','D:\deepseek\ctf-lab\lab-scaffold\l6-vault' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8091','-t','D:\deepseek\ctf-lab\lab-scaffold\l7-escape' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8092','-t','D:\deepseek\ctf-lab\lab-scaffold\l8-phar' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8093','-t','D:\deepseek\ctf-lab\lab-scaffold\l9-native' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8094','-t','D:\deepseek\ctf-lab\lab-scaffold\l9-inner' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8095','-t','D:\deepseek\ctf-lab\lab-scaffold\l10-phpggc' -WorkingDirectory 'D:\deepseek\ctf-lab\lab-scaffold\l10-phpggc' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'python' -ArgumentList 'D:\deepseek\ctf-lab\lab-scaffold\l11-pickle\server.py' -WorkingDirectory 'D:\deepseek\ctf-lab\lab-scaffold\l11-pickle' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'java' -ArgumentList '-cp','.;commons-collections-3.1.jar','L12Server' -WorkingDirectory 'D:\deepseek\ctf-lab\lab-scaffold\l12-java' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\ctf-lab\lab-scaffold\l13-dotnet\L13Server.exe' -WorkingDirectory 'D:\deepseek\ctf-lab\lab-scaffold\l13-dotnet' -WindowStyle Hidden"
powershell -NoProfile -Command "Start-Process -FilePath 'D:\deepseek\php709\php.exe' -ArgumentList '-S','127.0.0.1:8099','-t','D:\deepseek\ctf-lab\lab-scaffold\l14-exam' -WorkingDirectory 'D:\deepseek\ctf-lab\lab-scaffold\l14-exam' -WindowStyle Hidden"
echo L6=8090  L7=8091  L8=8092  L9=8093  L9inner=8094  L10=8095  L11=8096  L12=8097  L13=8098  L14exam=8099
echo stop: Task Manager -^> kill php.exe (no windows anymore)
timeout /t 3 >nul
