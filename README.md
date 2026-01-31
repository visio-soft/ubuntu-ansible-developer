# bOOT - PHP Developer Environment

A professional, local PHP development stack for Ubuntu, inspired by Laravel Herd. This project provides an automated Ansible system provisioner and a sleek Laravel dashboard to manage your development workflows.

### 🚀 Features

- **Dynamic Project Management**: Create Laravel projects via Git URLs with built-in SSH access validation.
- **Service Dashboard**: Real-time status, restart controls, and log viewing for Nginx, PHP 8.4 FPM, PostgreSQL, and Redis.
- **Herd-style Log Viewer**: High-performance log tailing with:
    - Live search and filtering.
    - Auto-refreshing entries.
    - **IDE Integration**: Click stack trace file paths to jump directly to VS Code.
- **Software Center**: One-click installation of essential dev tools (VS Code, Chrome, TablePlus, DBeaver).
- **Database Management**: Instant PostgreSQL database creation and a "Shortcut to TablePlus" with pre-filled connection strings.
- **Modern UI**: Clean, Apple-inspired interface with Glassmorphism and dark/light support.

---

### 🛠️ Components

#### 1. Ansible System Provisioner
The `setup.yml` playbook configures the base operating system with:
- PHP 8.4 (FPM & CLI)
- Nginx
- PostgreSQL
- Redis
- Node.js & Composer
- Supervisor (for background workers)

#### 2. The Manager App
Located in the `/manager` directory, this Laravel 12 application serves as the command center for your development environment.

---

### 📥 Getting Started

1. **Initial Provisioning**:
   Ensure you have Ansible installed, then run the setup script:
   ```bash
   ansible-playbook setup.yml
   ```

2. **The Manager Interface**:
   Access the web interface at:
   [http://manager.test](http://manager.test)

3. **Creating Projects**:
   - Navigate to **Projects**.
   - Provide a Git SSH URL (e.g., `git@github.com:user/repo.git`).
   - Use the **Check Access** button to verify your SSH keys.
   - Choose to install **Laravel Horizon** optionally during setup.

---

### 🖥️ Software & Services

Monitor and manage your system from the **Services & Logs** section. You can:
- Tail system logs or project-specific logs.
- Edit your `php.ini` directly from the UI with auto-restart.
- Install or update development software via the **Software Center**.

---

### 📂 Directory Structure

- `/manager`: The Laravel Management Dashboard.
- `setup.yml`: Main Ansible system playbook.
- `projects.yml`: (Legacy) project definitions.
- `software.yml`: (Legacy) software definitions.

---

*Built with ❤️ for Ubuntu PHP Developers.*
