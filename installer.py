"""
Academic+ — Tenant Installer GUI
Automates full SaaS tenant provisioning on HestiaCP VPS.
Based on the working Bills installer architecture: SSH as admin with sudo -S for
HestiaCP commands, and sudo -u for Laravel/Composer commands.
"""
import tkinter as tk
from tkinter import ttk, messagebox, scrolledtext
import threading
import subprocess
import sys
import re
import secrets
import string

# Auto-install paramiko if missing
try:
    import paramiko
except ImportError:
    subprocess.check_call([sys.executable, "-m", "pip", "install", "paramiko"])
    import paramiko

# ═══════════════════════════════════════════
# VPS Profiles Configuration
# ═══════════════════════════════════════════
VPS_PROFILES = {
    "Gridbase SRV1 (68.168.218.151)": {
        "VPS_IP": "68.168.218.151",
        "VPS_USER": "admin",
        "VPS_PASS": "4c7HVxX#",
        "HESTIA_USER": "grupaqgl",
        "SOURCE_DOMAIN": "sga.gridbase.com.do",
    },
    "Gridbase SRV2 (162.35.181.41)": {
        "VPS_IP": "162.35.181.41",
        "VPS_USER": "admin",
        "VPS_PASS": "SamDP_9903",
        "HESTIA_USER": "grupaqgl",
        "SOURCE_DOMAIN": "sga.gridbase.com.do",
    }
}

# Configuración global del instalador y del proyecto
HESTIA_BIN = "/usr/local/hestia/bin"
GITHUB_REPO = "https://github.com/EasternSam/SGA-PADRE.git"
DEFAULT_ADMIN_EMAIL = "soporte@gridbase.com.do"
DEFAULT_ADMIN_PASS = "SamDP_9903"
PHP_VERSION = "PHP-8_3"
PROXY_TPL = "default"
WEB_TPL = "laravel"


def generate_db_password(length=16):
    """Generate a strong random password for the database."""
    chars = string.ascii_letters + string.digits
    return ''.join(secrets.choice(chars) for _ in range(length))


class InstallerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("Academic+ — Instalador de Instituciones")
        self.root.geometry("780x825")
        self.root.resizable(True, True)
        self.root.configure(bg="#0f172a")

        self.ssh = None
        self.is_running = False
        self.selected_vps_var = tk.StringVar()
        self._current_vps_pass = ""

        self._build_ui()
        self.root.protocol("WM_DELETE_WINDOW", self._on_close)

    def _build_ui(self):
        is_mac = (sys.platform == "darwin")
        font_family = "Segoe UI" if sys.platform == "win32" else "Helvetica"
        
        style = ttk.Style()
        if not is_mac:
            style.theme_use("clam")
            style.configure("Accent.TButton",
                            font=(font_family, 11, "bold"),
                            foreground="#ffffff", background="#6366f1",
                            padding=(20, 10))
            style.map("Accent.TButton",
                      background=[("active", "#818cf8"), ("disabled", "#334155")])
            style.configure("Danger.TButton",
                            font=(font_family, 10),
                            foreground="#ffffff", background="#ef4444",
                            padding=(15, 8))
            style.map("Danger.TButton",
                      background=[("active", "#f87171"), ("disabled", "#334155")])
        else:
            style.configure("TButton", font=(font_family, 12))

        # Header
        header = tk.Frame(self.root, bg="#0f172a")
        header.pack(fill="x", padx=24, pady=(20, 4))
        tk.Label(header, text="Academic+ Installer",
                 font=(font_family, 18, "bold"),
                 foreground="#f8fafc", background="#0f172a").pack(anchor="w")
        tk.Label(header, text="Provisión automática de instituciones (tenants) en el VPS",
                 font=(font_family, 10),
                 foreground="#94a3b8", background="#0f172a").pack(anchor="w", pady=(2, 0))

        sep = tk.Frame(self.root, height=1, bg="#334155")
        sep.pack(fill="x", padx=24, pady=12)

        # Input Card
        card = tk.Frame(self.root, bg="#1e293b", highlightbackground="#334155", highlightthickness=1)
        card.pack(fill="x", padx=24, pady=(0, 8))
        card_inner = tk.Frame(card, bg="#1e293b")
        card_inner.pack(fill="x", padx=20, pady=16)

        # VPS Selector
        tk.Label(card_inner, text="Servidor VPS Destino",
                 font=(font_family, 10, "bold"),
                 foreground="#cbd5e1", background="#1e293b").grid(row=0, column=0, columnspan=2, sticky="w", pady=(0, 4))
        self.vps_combobox = ttk.Combobox(card_inner, textvariable=self.selected_vps_var,
                                         values=list(VPS_PROFILES.keys()), state="readonly",
                                         font=("Consolas", 11))
        self.vps_combobox.grid(row=1, column=0, columnspan=2, sticky="ew", ipady=4, pady=(0, 8))
        self.vps_combobox.current(0)

        # Domain
        tk.Label(card_inner, text="Dominio de la Institución",
                 font=(font_family, 10, "bold"),
                 foreground="#cbd5e1", background="#1e293b").grid(row=2, column=0, sticky="w", pady=(0, 4))
        self.domain_var = tk.StringVar()
        self.domain_entry = tk.Entry(card_inner, textvariable=self.domain_var,
                                     font=("Consolas", 13), bg="#0f172a",
                                     fg="#f8fafc", insertbackground="#6366f1",
                                     relief="flat", bd=0, highlightthickness=1,
                                     highlightcolor="#6366f1", highlightbackground="#334155")
        self.domain_entry.grid(row=3, column=0, sticky="ew", ipady=8, padx=(0, 12))
        self.domain_entry.insert(0, "sga.institucion.edu.do")
        self.domain_entry.bind("<FocusIn>", lambda e: (
            self.domain_entry.delete(0, "end")
            if self.domain_entry.get() == "sga.institucion.edu.do" else None))
        tk.Label(card_inner, text="Ej: sga.centu.edu.do",
                 font=(font_family, 9),
                 foreground="#94a3b8", background="#1e293b").grid(row=4, column=0, sticky="w", pady=(2, 0))

        # Company
        tk.Label(card_inner, text="Nombre de la Institución",
                 font=(font_family, 10, "bold"),
                 foreground="#cbd5e1", background="#1e293b").grid(row=2, column=1, sticky="w", pady=(0, 4))
        self.company_var = tk.StringVar()
        self.company_entry = tk.Entry(card_inner, textvariable=self.company_var,
                                      font=("Consolas", 13), bg="#0f172a",
                                      fg="#f8fafc", insertbackground="#6366f1",
                                      relief="flat", bd=0, highlightthickness=1,
                                      highlightcolor="#6366f1", highlightbackground="#334155")
        self.company_entry.grid(row=3, column=1, sticky="ew", ipady=8)
        self.company_entry.insert(0, "CENTU")
        self.company_entry.bind("<FocusIn>", lambda e: (
            self.company_entry.delete(0, "end")
            if self.company_entry.get() == "CENTU" else None))
        tk.Label(card_inner, text="Nombre comercial visible en el sistema",
                 font=(font_family, 9),
                 foreground="#94a3b8", background="#1e293b").grid(row=4, column=1, sticky="w", pady=(2, 0))

        # License
        tk.Label(card_inner, text="Clave de Licencia Academic+",
                 font=(font_family, 10, "bold"),
                 foreground="#cbd5e1", background="#1e293b").grid(row=5, column=0, columnspan=2, sticky="w", pady=(12, 4))
        self.license_var = tk.StringVar()
        self.license_entry = tk.Entry(card_inner, textvariable=self.license_var,
                                      font=("Consolas", 13), bg="#0f172a",
                                      fg="#f8fafc", insertbackground="#6366f1",
                                      relief="flat", bd=0, highlightthickness=1,
                                      highlightcolor="#6366f1", highlightbackground="#334155")
        self.license_entry.grid(row=6, column=0, columnspan=2, sticky="ew", ipady=8)
        self.license_entry.insert(0, "APLUS-CE22-F0WM-NI9Y")
        tk.Label(card_inner, text="Clave para validar con el servidor central de GridBase",
                 font=(font_family, 9),
                 foreground="#94a3b8", background="#1e293b").grid(row=7, column=0, columnspan=2, sticky="w", pady=(2, 0))

        card_inner.columnconfigure(0, weight=1)
        card_inner.columnconfigure(1, weight=1)

        # Buttons
        btn_frame = tk.Frame(self.root, bg="#0f172a")
        btn_frame.pack(fill="x", padx=24, pady=(8, 4))
        if not is_mac:
            self.install_btn = ttk.Button(btn_frame, text="Instalar Institución",
                                          style="Accent.TButton", command=self._start_install)
            self.uninstall_btn = ttk.Button(btn_frame, text="Desinstalar",
                                            style="Danger.TButton", command=self._start_uninstall)
        else:
            self.install_btn = ttk.Button(btn_frame, text="Instalar Institución", command=self._start_install)
            self.uninstall_btn = ttk.Button(btn_frame, text="Desinstalar", command=self._start_uninstall)
        self.install_btn.pack(side="left")
        self.uninstall_btn.pack(side="left", padx=(12, 0))
        self.status_label = tk.Label(btn_frame, text="Listo",
                                     font=(font_family, 10),
                                     foreground="#38bdf8", background="#0f172a")
        self.status_label.pack(side="right")

        # Progress
        self.progress = ttk.Progressbar(self.root, mode="determinate", maximum=100, value=0)
        self.progress.pack(fill="x", padx=24, pady=(8, 4))

        # Log
        log_label = tk.Label(self.root, text="Registro de Actividad",
                             font=(font_family, 10, "bold"),
                             foreground="#cbd5e1", background="#0f172a")
        log_label.pack(anchor="w", padx=24, pady=(8, 2))
        self.log_text = scrolledtext.ScrolledText(
            self.root, font=("Consolas", 10), bg="#020617", fg="#e2e8f0",
            insertbackground="#6366f1", relief="flat", bd=0, wrap="word",
            highlightthickness=1, highlightbackground="#1e293b", height=16)
        self.log_text.pack(fill="both", expand=True, padx=24, pady=(0, 16))
        self.log_text.tag_configure("success", foreground="#4ade80")
        self.log_text.tag_configure("error", foreground="#f87171")
        self.log_text.tag_configure("info", foreground="#38bdf8")
        self.log_text.tag_configure("step", foreground="#a78bfa", font=("Consolas", 10, "bold"))
        self.log_text.tag_configure("warn", foreground="#fbbf24")

    # ═══════════════════════════════════════
    # Logging Helpers
    # ═══════════════════════════════════════
    def log(self, msg, tag="info"):
        self.log_text.insert("end", msg + "\n", tag)
        self.log_text.see("end")
        self.root.update_idletasks()

    def set_status(self, text):
        self.status_label.configure(text=text)
        self.root.update_idletasks()

    def set_progress(self, value):
        self.progress["value"] = value
        self.root.update_idletasks()

    # ═══════════════════════════════════════
    # SSH Helpers — ALL commands go through sudo -S
    # ═══════════════════════════════════════
    def ssh_connect(self, vps_ip, vps_user, vps_pass):
        self.log(f"Conectando al VPS ({vps_ip}) como '{vps_user}'...", "info")
        self.ssh = paramiko.SSHClient()
        self.ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self.ssh.connect(vps_ip, username=vps_user, password=vps_pass, timeout=15)
        self._current_vps_pass = vps_pass
        self.log(f"Conectado exitosamente", "success")

    def ssh_run(self, cmd, label=None):
        """Execute a command via SSH. ALL commands are wrapped in sudo -S
        so they run as root, matching the behaviour of the original
        installer that connects as root directly."""
        if label:
            self.log(f"  → {label}", "info")

        # Wrap every command in: echo 'password' | sudo -S bash -c '...'
        # This guarantees root execution for every single command,
        # including multi-line scripts and chained && commands.
        escaped_cmd = cmd.replace("'", "'\\''")
        final_cmd = f"echo '{self._current_vps_pass}' | sudo -S bash -c '{escaped_cmd}'"

        stdin, stdout, stderr = self.ssh.exec_command(final_cmd, timeout=300)
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()

        if out:
            for line in out.split("\n")[:20]:
                self.log(f"    {line}")

        if err:
            lines = err.split("\n")
            filtered = [l for l in lines
                        if "[sudo] password" not in l.lower()
                        and "warning" not in l.lower()
                        and "composer" not in l.lower()]
            if filtered:
                for line in filtered[:10]:
                    self.log(f"    [ALERTA] {line}", "warn")

        return out, err

    def ssh_close(self):
        if self.ssh:
            self.ssh.close()
            self.ssh = None

    # ═══════════════════════════════════════
    # Domain Parsing
    # ═══════════════════════════════════════
    def parse_domain(self, domain):
        domain = domain.strip().lower()
        domain = re.sub(r'^https?://', '', domain)
        domain = domain.rstrip('/')
        for tld in ['.com.do', '.edu.do', '.org.do', '.net.do', '.gob.do',
                    '.do', '.com', '.net', '.org', '.io', '.co']:
            if domain.endswith(tld):
                domain_no_tld = domain[:-len(tld)]
                break
        else:
            domain_no_tld = domain
        parts = domain_no_tld.split('.')
        slug = parts[-1]
        slug = re.sub(r'[^a-z0-9]', '_', slug)
        return domain, slug

    # ═══════════════════════════════════════
    # INSTALL FLOW
    # ═══════════════════════════════════════
    def _start_install(self):
        if self.is_running:
            return
        domain_raw = self.domain_var.get().strip()
        company = self.company_var.get().strip()
        license_key = self.license_var.get().strip()
        if not domain_raw or domain_raw == "sga.institucion.edu.do":
            messagebox.showwarning("Dominio requerido",
                                   "Por favor ingresa el dominio de la institución.")
            return
        if not company or company == "CENTU":
            messagebox.showwarning("Nombre requerido",
                                   "Por favor ingresa el nombre de la institución.")
            return
        if not license_key:
            messagebox.showwarning("Licencia requerida",
                                   "Por favor ingresa la clave de licencia para Academic+.")
            return
        self.is_running = True
        self.install_btn.configure(state="disabled")
        self.uninstall_btn.configure(state="disabled")
        self.log_text.delete("1.0", "end")
        self.set_progress(0)
        threading.Thread(target=self._install_worker,
                         args=(domain_raw, company, license_key), daemon=True).start()

    def _install_worker(self, domain_raw, company, license_key):
        try:
            vps_name = self.selected_vps_var.get()
            vps_config = VPS_PROFILES.get(vps_name)
            if not vps_config:
                raise Exception("VPS no válido seleccionado.")

            vps_ip = vps_config["VPS_IP"]
            vps_user = vps_config["VPS_USER"]
            vps_pass = vps_config["VPS_PASS"]
            hestia_user = vps_config["HESTIA_USER"]
            source_domain = vps_config["SOURCE_DOMAIN"]
            source_dir = f"/home/{hestia_user}/web/{source_domain}/public_html"

            domain, slug = self.parse_domain(domain_raw)
            db_name = f"{hestia_user}_{slug}"
            db_user = f"{hestia_user}_{slug}"
            db_pass = generate_db_password()
            app_dir = f"/home/{hestia_user}/web/{domain}/public_html"

            self.log(f"═══════════════════════════════════════", "step")
            self.log(f"  INSTALANDO EN: {vps_name} ({vps_ip})", "step")
            self.log(f"  Institución: {company}  |  Dominio: {domain}", "step")
            self.log(f"  Base de datos: {db_name}", "step")
            self.log(f"═══════════════════════════════════════\n", "step")

            # ── Connect ──
            self.set_status("Conectando al VPS...")
            self.set_progress(5)
            self.ssh_connect(vps_ip, vps_user, vps_pass)

            # ── STEP 0: Ensure laravel nginx template exists ──
            self.set_status("Preparando plantillas...")
            self.set_progress(7)
            self.log("\nPASO 0: Verificar plantillas del servidor web", "step")
            # Create laravel templates for Apache2 and Nginx if they don't exist
            # KEY: .tpl uses %docroot%, .stpl (SSL) uses %sdocroot%
            self.ssh_run(
                # Apache2 php-fpm templates
                'APACHE_DIR="/usr/local/hestia/data/templates/web/apache2/php-fpm"; '
                'if [ -d "$APACHE_DIR" ] && [ ! -f "$APACHE_DIR/laravel.tpl" ]; then '
                '  cp "$APACHE_DIR/default.tpl" "$APACHE_DIR/laravel.tpl"; '
                '  cp "$APACHE_DIR/default.stpl" "$APACHE_DIR/laravel.stpl"; '
                # .tpl (HTTP) uses %docroot%
                '  sed -i "s|DocumentRoot %docroot%$|DocumentRoot %docroot%/public|" "$APACHE_DIR/laravel.tpl"; '
                '  sed -i "s|<Directory %docroot%>|<Directory %docroot%/public>|" "$APACHE_DIR/laravel.tpl"; '
                # .stpl (SSL) uses %sdocroot%
                '  sed -i "s|DocumentRoot %sdocroot%$|DocumentRoot %sdocroot%/public|" "$APACHE_DIR/laravel.stpl"; '
                '  sed -i "s|<Directory %sdocroot%>|<Directory %sdocroot%/public>|" "$APACHE_DIR/laravel.stpl"; '
                '  echo "Apache2 laravel templates creados"; '
                'fi; '
                # Nginx proxy templates
                'NGINX_DIR="/usr/local/hestia/data/templates/web/nginx"; '
                'if [ -d "$NGINX_DIR" ] && [ ! -f "$NGINX_DIR/laravel.tpl" ]; then '
                '  cp "$NGINX_DIR/default.tpl" "$NGINX_DIR/laravel.tpl"; '
                '  cp "$NGINX_DIR/default.stpl" "$NGINX_DIR/laravel.stpl"; '
                '  sed -i "s|%docroot%;|%docroot%/public;|g" "$NGINX_DIR/laravel.tpl"; '
                '  sed -i "s|%sdocroot%;|%sdocroot%/public;|g" "$NGINX_DIR/laravel.stpl"; '
                '  echo "Nginx laravel templates creados"; '
                'fi; '
                # Nginx php-fpm templates
                'NGINX_FPM="/usr/local/hestia/data/templates/web/nginx/php-fpm"; '
                'if [ -d "$NGINX_FPM" ] && [ ! -f "$NGINX_FPM/laravel.tpl" ]; then '
                '  cp "$NGINX_FPM/default.tpl" "$NGINX_FPM/laravel.tpl"; '
                '  cp "$NGINX_FPM/default.stpl" "$NGINX_FPM/laravel.stpl"; '
                '  sed -i "s|%docroot%;|%docroot%/public;|g" "$NGINX_FPM/laravel.tpl"; '
                '  sed -i "s|%sdocroot%;|%sdocroot%/public;|g" "$NGINX_FPM/laravel.stpl"; '
                '  echo "Nginx php-fpm laravel templates creados"; '
                'fi; '
                'echo "Templates verificados"',
                "Verificando/creando plantillas web Laravel..."
            )

            # ── STEP 1: Create Web Domain ──
            self.set_status("Creando dominio web...")
            self.set_progress(10)
            self.log("\nPASO 1: Crear dominio web en HestiaCP", "step")
            out, err = self.ssh_run(
                f"{HESTIA_BIN}/v-add-web-domain {hestia_user} {domain}",
                "Registrando dominio..."
            )
            if "already exists" in (out + err).lower():
                self.log("  [ALERTA] El dominio ya existe en HestiaCP. Continuando...", "warn")
            else:
                self.log(f"  [OK] Dominio {domain} creado", "success")

            # Set PHP version and web template
            self.set_progress(15)
            self.ssh_run(
                f"{HESTIA_BIN}/v-change-web-domain-backend-tpl {hestia_user} {domain} {PHP_VERSION}",
                "Configurando PHP 8.3..."
            )
            self.ssh_run(
                f"{HESTIA_BIN}/v-change-web-domain-tpl {hestia_user} {domain} {WEB_TPL}",
                "Aplicando template Laravel..."
            )
            # Rebuild to regenerate Apache/Nginx config files with the new template
            self.ssh_run(
                f"{HESTIA_BIN}/v-rebuild-web-domains {hestia_user}",
                "Regenerando configuración web (Apache/Nginx)..."
            )

            # ── STEP 2: SSL ──
            self.set_status("Generando SSL autofirmado...")
            self.set_progress(20)
            self.log("\nPASO 2: Certificado SSL Autofirmado (Optimizado para Cloudflare Full)", "step")
            out_gen, err_gen = self.ssh_run(
                f"{HESTIA_BIN}/v-generate-ssl-cert {domain} admin@{domain} DO SD SD 'GridBase' IT ''",
                "Generando certificado autofirmado en el VPS..."
            )
            ssl_ok = False
            match = re.search(r'[Dd]irectory:\s+(\S+)', out_gen)
            if match:
                ssl_dir = match.group(1)
                self.log(f"  → Certificado temporal en {ssl_dir}", "info")
                self.ssh_run(
                    f"{HESTIA_BIN}/v-add-web-domain-ssl {hestia_user} {domain} {ssl_dir}",
                    "Instalando certificado en HestiaCP..."
                )
                out_check, _ = self.ssh_run(f"{HESTIA_BIN}/v-list-web-domain {hestia_user} {domain} json")
                try:
                    import json
                    info = json.loads(out_check)
                    if info.get(domain, {}).get("SSL") == "yes":
                        ssl_ok = True
                except:
                    pass
            if ssl_ok:
                self.log("  SSL autofirmado configurado y activo.", "success")
            else:
                self.log("  [WARN] No se pudo confirmar SSL. Continuando...", "warn")

            # ── STEP 3: Create Database ──
            self.set_status("Creando base de datos...")
            self.set_progress(30)
            self.log(f"\nPASO 3: Crear base de datos ({db_name})", "step")
            out, err = self.ssh_run(
                f"{HESTIA_BIN}/v-add-database {hestia_user} {slug} {slug} {db_pass} mysql",
                "Creando base de datos y usuario..."
            )
            if "already exists" in (out + err).lower():
                self.log("  [ALERTA] La base de datos ya existe. Reutilizando...", "warn")
                out2, _ = self.ssh_run(f"{HESTIA_BIN}/v-list-databases {hestia_user} json")
                try:
                    import json
                    dbs = json.loads(out2)
                    real_info = dbs.get(db_name, {})
                    db_user = real_info.get("DBUSER", db_user)
                except:
                    pass
                db_pass = generate_db_password()
                self.ssh_run(
                    f"{HESTIA_BIN}/v-change-database-password {hestia_user} {db_name} {db_pass}",
                    "Reseteando contraseña de la DB..."
                )
                self.log(f"  [OK] DB reutilizada: {db_name} | User: {db_user}", "success")
            else:
                self.log(f"  [OK] DB: {db_name} | User: {db_user}", "success")
            self.log(f"   Password: {db_pass}", "info")

            # ── STEP 4: Deploy code ──
            self.set_status("Desplegando código...")
            self.set_progress(40)
            self.log(f"\nPASO 4: Desplegar código fuente", "step")

            # Check if source exists, copy or clone
            self.ssh_run(
                f'if [ -d "{source_dir}" ]; then '
                f'  echo "COPIANDO desde instalación local..."; '
                f'  rm -rf {app_dir}/* {app_dir}/.[!.]* 2>/dev/null; '
                f'  cp -a {source_dir}/. {app_dir}/; '
                f'else '
                f'  echo "CLONANDO desde GitHub..."; '
                f'  rm -rf {app_dir}/* {app_dir}/.[!.]* 2>/dev/null; '
                f'  git clone {GITHUB_REPO} {app_dir}; '
                f'fi',
                "Desplegando código fuente..."
            )

            # Purge cached config
            self.ssh_run(
                f"rm -f {app_dir}/bootstrap/cache/config.php "
                f"{app_dir}/bootstrap/cache/routes-v7.php "
                f"{app_dir}/bootstrap/cache/packages.php "
                f"{app_dir}/bootstrap/cache/services.php "
                f"{app_dir}/.env",
                "Limpiando caché y .env del origen..."
            )

            # Remove Force HTTPS from .htaccess if it exists
            self.ssh_run(
                f"test -f {app_dir}/.htaccess && "
                f"sed -i '/# Force HTTPS/,/RewriteRule.*https.*\\[L,R=301\\]/d' {app_dir}/.htaccess || true",
                "Eliminando Force HTTPS del .htaccess (si existe)..."
            )
            # Remove RequestReadTimeout from public/.htaccess (not allowed in .htaccess context)
            self.ssh_run(
                f"test -f {app_dir}/public/.htaccess && "
                f"sed -i '/RequestReadTimeout/d' {app_dir}/public/.htaccess || true",
                "Eliminando RequestReadTimeout del .htaccess (incompatible con Apache .htaccess)..."
            )
            self.set_progress(50)
            self.log("  [OK] Código desplegado", "success")

            # ── STEP 5: Write .env ──
            self.set_status("Configurando entorno...")
            self.set_progress(55)
            self.log(f"\nPASO 5: Configurar .env", "step")

            env_content = f'''APP_NAME="SGA {company}"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://{domain}

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE="{db_name}"
DB_USERNAME="{db_user}"
DB_PASSWORD='{db_pass}'

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@{domain}"
MAIL_FROM_NAME="{company}"

WP_API_BASE_URI=
WP_API_SECRET=

CARDNET_ENV=sandbox
CARDNET_MERCHANT_ID=349000000
CARDNET_TERMINAL_ID=
CARDNET_IMAGE_URL=

MOODLE_URL=
MOODLE_TOKEN=

SAAS_MODE_ENABLED=false
APP_LICENSE_KEY="{license_key}"
LICENSE_KEY="{license_key}"
APP_INSTALLED=true
APLUSMASTER_URL=https://gestion.90s.agency/api/v1/validate-license
'''
            self.ssh_run(
                f"cat > {app_dir}/.env << 'ENVEOF'\n{env_content}\nENVEOF",
                "Escribiendo archivo .env..."
            )
            self.log("  [OK] Archivo .env creado", "success")

            # ── STEP 5b: Permissions ──
            self.set_status("Configurando permisos...")
            self.set_progress(60)
            self.log(f"\nPASO 5b: Permisos y directorios", "step")
            self.ssh_run(
                f"chown -R {hestia_user}:{hestia_user} {app_dir} && "
                f"mkdir -p {app_dir}/storage/framework/cache/data "
                f"{app_dir}/storage/framework/sessions "
                f"{app_dir}/storage/framework/views "
                f"{app_dir}/storage/logs "
                f"{app_dir}/bootstrap/cache && "
                f"chown -R {hestia_user}:www-data {app_dir}/storage {app_dir}/bootstrap/cache && "
                f"chmod -R 775 {app_dir}/storage {app_dir}/bootstrap/cache",
                "Aplicando permisos..."
            )
            self.log("  [OK] Permisos configurados", "success")

            # ── STEP 6: Ensure Composer is installed ──
            self.set_status("Verificando Composer...")
            self.set_progress(63)
            self.log(f"\nPASO 6: Verificar/Instalar Composer", "step")
            self.ssh_run(
                'if ! command -v composer &>/dev/null && [ ! -f /usr/local/bin/composer ]; then '
                '  echo "Composer NO encontrado. Instalando..."; '
                '  cd /tmp && '
                '  curl -sS https://getcomposer.org/installer | php && '
                '  mv composer.phar /usr/local/bin/composer && '
                '  chmod +x /usr/local/bin/composer && '
                '  echo "Composer instalado exitosamente"; '
                'else '
                '  echo "Composer ya está instalado: $(which composer 2>/dev/null || echo /usr/local/bin/composer)"; '
                'fi',
                "Verificando Composer..."
            )

            # ── STEP 6b: Composer install (BEFORE artisan!) ──
            self.set_status("Instalando dependencias...")
            self.set_progress(67)
            self.log(f"\nPASO 6b: Composer install", "step")
            # Run composer as hestia_user with explicit PATH
            self.ssh_run(
                f"cd {app_dir} && "
                f"sudo -u {hestia_user} "
                f"env COMPOSER_HOME=/home/{hestia_user}/.composer "
                f"/usr/local/bin/composer install "
                f"--no-dev --optimize-autoloader --no-interaction 2>&1 | tail -15",
                "Instalando paquetes de PHP..."
            )
            self.set_progress(75)
            self.log("  [OK] Dependencias instaladas", "success")

            # ── STEP 7b: Generate APP_KEY ──
            self.set_status("Generando clave de aplicación...")
            self.set_progress(78)
            self.log(f"\nPASO 7: Generar APP_KEY", "step")
            self.ssh_run(
                f"cd {app_dir} && sudo -u {hestia_user} php artisan config:clear",
                "Limpiando caché de configuración..."
            )
            self.ssh_run(
                f"cd {app_dir} && sudo -u {hestia_user} php artisan key:generate --force",
                "Generando APP_KEY..."
            )

            # ── STEP 8: Migrations & Seeds ──
            self.set_status("Ejecutando migraciones...")
            self.set_progress(82)
            self.log(f"\nPASO 8: Migraciones y Seeds", "step")
            self.ssh_run(
                f"cd {app_dir} && sudo -u {hestia_user} php artisan config:clear",
                "Asegurando configuración limpia..."
            )
            # Verify APP_KEY
            out_key, _ = self.ssh_run(f"grep APP_KEY {app_dir}/.env")
            if "APP_KEY=" in out_key and "base64:" not in out_key:
                self.log("  [ALERTA] APP_KEY vacía, regenerando...", "warn")
                self.ssh_run(
                    f"cd {app_dir} && sudo -u {hestia_user} php artisan key:generate --force",
                    "Re-generando APP_KEY..."
                )
            self.ssh_run(
                f"cd {app_dir} && sudo -u {hestia_user} php artisan migrate --force",
                "Creando tablas..."
            )
            self.ssh_run(
                f"cd {app_dir} && sudo -u {hestia_user} php artisan db:seed --force",
                "Insertando datos iniciales..."
            )
            self.set_progress(90)
            self.log("  [OK] Base de datos inicializada", "success")

            # ── STEP 9: Cache optimization ──
            self.set_status("Optimizando caché...")
            self.set_progress(95)
            self.log(f"\nPASO 9: Optimización de caché", "step")
            self.ssh_run(
                f"cd {app_dir} && sudo -u {hestia_user} php artisan config:cache && "
                f"sudo -u {hestia_user} php artisan route:cache && "
                f"sudo -u {hestia_user} php artisan view:cache",
                "Optimizando Laravel..."
            )
            self.log("  [OK] Caché optimizada", "success")

            # ── DONE ──
            self.set_progress(100)
            self.set_status("¡Instalación completada!")
            self.log(f"\n{'═' * 50}", "success")
            self.log(f"  ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!", "success")
            self.log(f"{'═' * 50}", "success")
            self.log(f"   URL:      https://{domain}", "success")
            self.log(f"   Email:    {DEFAULT_ADMIN_EMAIL}", "success")
            self.log(f"   Password: {DEFAULT_ADMIN_PASS}", "success")
            self.log(f"   DB:       {db_name}", "info")
            self.log(f"   DB Pass:  {db_pass}", "info")
            self.log(f"{'═' * 50}\n", "success")

            self.root.after(0, lambda: messagebox.showinfo(
                "[OK] Instalación Exitosa",
                f"El sistema ha sido instalado en:\n\n"
                f" https://{domain}\n\n"
                f" {DEFAULT_ADMIN_EMAIL}\n"
                f" {DEFAULT_ADMIN_PASS}\n\n"
                f"El asistente de configuración inicial aparecerá\n"
                f"en el primer inicio de sesión."
            ))

        except Exception as e:
            error_msg = str(e)
            self.log(f"\nERROR: {error_msg}", "error")
            self.set_status("Error en la instalación")
            self.root.after(0, lambda msg=error_msg: messagebox.showerror(
                "Error", f"La instalación falló:\n{msg}"))
        finally:
            self.ssh_close()
            self.is_running = False
            self.root.after(0, lambda: self.install_btn.configure(state="normal"))
            self.root.after(0, lambda: self.uninstall_btn.configure(state="normal"))

    # ═══════════════════════════════════════
    # UNINSTALL FLOW
    # ═══════════════════════════════════════
    def _start_uninstall(self):
        if self.is_running:
            return
        domain_raw = self.domain_var.get().strip()
        if not domain_raw or domain_raw == "sga.institucion.edu.do":
            messagebox.showwarning("Dominio requerido",
                                   "Por favor ingresa el dominio a desinstalar.")
            return
        domain, slug = self.parse_domain(domain_raw)
        vps_name = self.selected_vps_var.get()
        vps_config = VPS_PROFILES.get(vps_name)
        if not vps_config:
            messagebox.showerror("Error", "VPS no válido seleccionado.")
            return
        source_domain = vps_config["SOURCE_DOMAIN"]
        hestia_user = vps_config["HESTIA_USER"]
        if domain == source_domain:
            messagebox.showerror("¡Protegido!",
                                 "No se puede desinstalar el dominio principal del sistema.")
            return
        if not messagebox.askyesno(
            "Confirmar Desinstalación",
            f"¿Estás seguro de que deseas ELIMINAR completamente?\n\n"
            f" Servidor VPS: {vps_name}\n"
            f" Dominio: {domain}\n"
            f" Base de datos: {hestia_user}_{slug}\n\n"
            f"Esta acción NO se puede deshacer.",
            icon="warning"):
            return
        self.is_running = True
        self.install_btn.configure(state="disabled")
        self.uninstall_btn.configure(state="disabled")
        self.log_text.delete("1.0", "end")
        self.set_progress(0)
        threading.Thread(target=self._uninstall_worker,
                         args=(domain, slug, vps_name), daemon=True).start()

    def _uninstall_worker(self, domain, slug, vps_name):
        try:
            vps_config = VPS_PROFILES.get(vps_name)
            if not vps_config:
                raise Exception("VPS no válido seleccionado.")
            vps_ip = vps_config["VPS_IP"]
            vps_user = vps_config["VPS_USER"]
            vps_pass = vps_config["VPS_PASS"]
            hestia_user = vps_config["HESTIA_USER"]
            db_name = f"{hestia_user}_{slug}"

            self.log(f"DESINSTALANDO DESDE: {vps_name} ({vps_ip})", "error")
            self.log(f"Dominio: {domain}\n", "error")

            self.set_status("Conectando al VPS...")
            self.set_progress(10)
            self.ssh_connect(vps_ip, vps_user, vps_pass)

            self.set_status("Eliminando base de datos...")
            self.set_progress(30)
            self.log(" Eliminando base de datos...", "step")
            self.ssh_run(
                f"{HESTIA_BIN}/v-delete-database {hestia_user} {db_name}",
                f"Eliminando {db_name}..."
            )
            self.log(f"  [OK] Base de datos {db_name} eliminada", "success")

            self.set_status("Eliminando dominio web...")
            self.set_progress(60)
            self.log(" Eliminando dominio web...", "step")
            self.ssh_run(
                f"{HESTIA_BIN}/v-delete-web-domain {hestia_user} {domain}",
                f"Eliminando {domain}..."
            )
            self.log(f"  [OK] Dominio {domain} eliminado", "success")

            self.set_progress(100)
            self.set_status("Desinstalación completada")
            self.log(f"\n{'═' * 50}", "success")
            self.log(f"  {domain} ha sido eliminado completamente del servidor {vps_name}.", "success")
            self.log(f"{'═' * 50}\n", "success")
            self.root.after(0, lambda: messagebox.showinfo(
                "Desinstalación completada",
                f"{domain} ha sido eliminado del servidor {vps_name}."))
        except Exception as e:
            self.log(f"\nERROR: {str(e)}", "error")
            self.set_status("Error")
        finally:
            self.ssh_close()
            self.is_running = False
            self.root.after(0, lambda: self.install_btn.configure(state="normal"))
            self.root.after(0, lambda: self.uninstall_btn.configure(state="normal"))

    def _on_close(self):
        if self.is_running:
            if not messagebox.askyesno("Proceso en curso",
                                       "Hay una instalación en progreso. ¿Cerrar de todos modos?"):
                return
        self.ssh_close()
        self.root.destroy()


if __name__ == "__main__":
    root = tk.Tk()
    app = InstallerApp(root)
    root.mainloop()
