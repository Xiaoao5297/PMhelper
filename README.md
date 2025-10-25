# PMhelper
[中文](#chs) [English](#eng)
### 中文 Chinese
<a id="chs"></a>
## PMhelper
`PMhelper`是一个旨在简化在移动设备上安装和运行 PocketMine 的项目。

### 启动和安装
- 确保您的设备使用 aarch64 CPU 架构。如果不是，请使用您自己的 PHP 二进制文件。
- 在[Termux](https://github.com/termux/termux-app)软件里运行以下命令
```bash
bash -c "$(curl -L https://raw.githubusercontent.com/Xiaoao5297/PMhelper/refs/heads/main/PMhelper.sh)"
```
- 随后弹出一个 dialog 窗口，使用上下方向键可以选择，输入回车键以选择。
- 安装后，PocketMine 通常会被安装在以下目录：`/data/data/com.termux/files/home/PocketMine/`或`$HOME/PocketMine/`
- 您可以在此文件夹的`bin/`文件夹里使用您自己的 php 二进制包

### English
<a id="eng"></a>
## PMhelper

`PMhelper` is a project designed to facilitate the installation and operation of PocketMine on mobile devices.

### Installation and Startup

- Ensure your device uses an aarch64 CPU architecture. If not, please use your own PHP binary.
- Run the following command in the [Termux](https://github.com/termux/termux-app) application:
```bash
bash -c "$(curl -L https://raw.githubusercontent.com/Xiaoao5297/PMhelper/refs/heads/main/PMhelper.sh)"
```
- A dialog window will appear. Use the Up/Down arrow keys to navigate and press Enter to confirm your selection.
- After installation, PocketMine will typically be installed in the following directory: 
`/data/data/com.termux/files/home/PocketMine` or `$HOME/PocketMine/`.
- You can use your own PHP binary in the 
`bin/` folder within this directory.