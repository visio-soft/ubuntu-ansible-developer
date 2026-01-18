# Ubuntu Developer Setup

Ansible playbooks for setting up a Laravel development environment on **Ubuntu 24.04**.

## � Quick Start

```bash
# Download and run
git clone https://github.com/visio-soft/ubuntu-ansible-developer.git
cd ubuntu-ansible-developer
chmod +x run.sh
./run.sh
```

Or **one-liner** (copy & paste):

```bash
git clone https://github.com/visio-soft/ubuntu-ansible-developer.git && cd ubuntu-ansible-developer && chmod +x run.sh && ./run.sh
```

## �📁 Dosya Yapısı

| Dosya | Açıklama |
|-------|----------|
| `software.yml` | Yazılım kurulumları (PHP, Node, DB, IDE) |
| `projects.yml` | Proje kurulumları (clone, migrate, horizon) |
| `run.sh` | İnteraktif kurulum scripti |

## 🎛️ Kurulum Menüsü

Script açıldığında tüm bileşenler seçili gelir:

```
[1] ✓ Sistem Paketleri (git, curl, acl, supervisor)
[2] ✓ PHP 8.4 + Composer + Extensions
[3] ✓ Node.js 20 + NPM
[4] ✓ PostgreSQL + Redis
[5] ✓ Nginx + Valet Linux
[6] ✓ VS Code + DBeaver
[7] ✓ Proje Kurulumları

[a] Tümünü Seç  [n] Tümünü Kaldır  [s] Başlat  [q] Çıkış
```

## ⚡ Hızlı Kurulum (Menüsüz)

```bash
./run.sh --all
```

## ⚙️ Proje Ayarları

`projects.yml` dosyasını düzenleyin:

```yaml
projects:
  - { name: "myapp", repo: "git@github.com:user/repo.git", db: "myapp_db", user: "myapp_user" }
```

**Proje dizini:** `/var/www/projects` (tüm www-data kullanıcıları erişebilir)

## 📊 Kurulum Sonrası

```bash
valet status                  # Valet kontrolü
sudo supervisorctl status     # Horizon kontrolü
```

Projeler: `http://proje-adi.test`
