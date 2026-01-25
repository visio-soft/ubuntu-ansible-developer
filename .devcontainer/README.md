# GitHub Codespaces Configuration

This directory contains the configuration for GitHub Codespaces to test the Ubuntu Developer Setup script on Ubuntu 24.04.

## 🚀 Quick Start with Codespaces

1. **Open in Codespaces:**
   - Go to the repository on GitHub
   - Click the green "Code" button
   - Select "Codespaces" tab
   - Click "Create codespace on main" (or your preferred branch)

2. **Wait for initialization:**
   - The Codespace will automatically create an Ubuntu 24.04 environment
   - The `run.sh` script will be made executable automatically

3. **Run the setup script:**
   ```bash
   ./run.sh
   ```
   Or use the non-interactive mode:
   ```bash
   ./run.sh --all
   ```

## 📋 What's Included

The devcontainer configuration provides:

- **Base Image:** Ubuntu 24.04 (microsoft/devcontainers/base:ubuntu-24.04)
- **User:** vscode (non-root user with sudo access)
- **Pre-installed:** Git, common utilities
- **VS Code Extensions:** PHP, Laravel, Docker, and EditorConfig support
- **Port Forwarding:** Ports 80, 443, 5432, 6379, 8000 for web, database, and Redis access

## 🔧 Testing the Setup

Once in the Codespace, you can test the installation:

```bash
# Run the interactive setup
./run.sh

# Or run all components automatically
./run.sh --all
```

After installation, verify services:

```bash
# Check service status
sudo systemctl status nginx postgresql redis-server

# Check PHP-FPM status (version installed by setup script)
sudo systemctl status php*-fpm

# Check Horizon status
supervisorctl status
```

## 🌐 Accessing Services

The following ports are automatically forwarded:
- **80, 443**: Nginx web server
- **5432**: PostgreSQL database
- **6379**: Redis
- **8000**: Laravel development server

You can access your applications through the Codespace's forwarded ports interface.

## 💡 Tips

- The Codespace uses a non-root user (`vscode`) by default
- All installations require `sudo` access (which is pre-configured)
- Services installed via the setup script will run natively in the Codespace
- The environment persists as long as the Codespace is active

## 🛠️ Customization

You can modify `.devcontainer/devcontainer.json` to:
- Add more VS Code extensions
- Configure additional port forwards
- Add custom post-create commands
- Install additional features

See [devcontainer.json reference](https://containers.dev/implementors/json_reference/) for all options.
