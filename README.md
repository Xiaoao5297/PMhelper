# PMhelper
[中文](#chs) [English](#eng)
### 中文 Chinese
<a id="chs"></a>
## PMhelper
`PMhelper`是一个便于在手机上安装和运行PocketMine的项目。

### 启动和安装
- 确保您的设备是aarch64地CPU架构，否则请使用自己的php二进制文件
- 在[Termux](https://github.com/termux/termux-app)软件里运行以下命令
```bash
bash -c "$(curl -L https://raw.githubusercontent.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/refs/heads/main/PMHelper.sh)"
```
- 随后弹出一个dialog窗口，使用上下方向键可以选择，输入回车键以选择。
- 安装完成后，PocketMine地安装位置一般在`/data/data/com.termux/files/home/PocketMine`文件夹下
- 您可以在此文件夹的`bin/`文件夹里使用您自己的php二进制包

### English
<a id="eng"></a>
## PMhelper

`PMhelper` is a project designed to facilitate the installation and operation of PocketMine on mobile devices.

### Installation and Startup

- Ensure your device uses an aarch64 CPU architecture. If not, please use your own PHP binary.
- Run the following command in the [Termux] (https://github.com/termux/termux-app) application:
```bash
bash -c "$(curl -L https://raw.githubusercontent.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/refs/heads/main/PMHelper.sh)"
```
- A dialog window will appear. Use the Up/Down arrow keys to navigate and press Enter to confirm your selection.
- After installation, PocketMine will typically be installed in the following directory: 
`/data/data/com.termux/files/home/PocketMine`.
- You can use your own PHP binary in the 
`bin/` folder within this directory.