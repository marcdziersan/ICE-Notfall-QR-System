import struct
import webbrowser
from time import sleep
from urllib.parse import quote
import secrets
import tkinter as tk
from tkinter import messagebox
from tkinter import ttk

import qrcode
from PIL import Image, ImageChops, ImageDraw, ImageFont, ImageOps

try:
    import serial
except ImportError:
    serial = None

# ==============================
# Drucker / App Konfiguration
# ==============================
COM_PORT = "COM11"
BAUDRATE = 9600
PRINTER_WIDTH = 384
DEFAULT_BASE_URL = "https://marcus-dziersan.net/ice/"
PREVIEW_FILE = "ice_qr_label_preview.png"


def normalize_base_url(url: str) -> str:
    url = (url or DEFAULT_BASE_URL).strip()
    if not url:
        url = DEFAULT_BASE_URL
    return url.rstrip("/") + "/"


def new_ice_key() -> str:
    # PHP-Admin und Public-Viewer erlauben A-Z, a-z, 0-9, _ und -.
    return secrets.token_urlsafe(18)


def build_ice_url(base_url: str, key: str) -> str:
    return normalize_base_url(base_url) + "?key=" + quote(key.strip(), safe="")


def build_admin_edit_url(base_url: str, key: str) -> str:
    return normalize_base_url(base_url) + "admin.php?action=edit&key=" + quote(key.strip(), safe="")


def load_font(size=26, bold=False):
    candidates = []
    if bold:
        candidates.extend([
            "arialbd.ttf",
            "C:/Windows/Fonts/arialbd.ttf",
            "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf",
        ])
    candidates.extend([
        "arial.ttf",
        "C:/Windows/Fonts/arial.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ])
    for path in candidates:
        try:
            return ImageFont.truetype(path, size)
        except Exception:
            pass
    return ImageFont.load_default()


def trim_image(im):
    bg = Image.new(im.mode, im.size, "white")
    diff = ImageChops.difference(im, bg)
    bbox = diff.getbbox()
    if bbox:
        return im.crop((bbox[0], bbox[1], bbox[2], min(bbox[3] + 28, im.height)))
    return im


def wrap_text(draw, text, font, max_width):
    lines = []
    for raw_line in str(text).splitlines():
        raw_line = raw_line.strip()
        if not raw_line:
            lines.append("")
            continue
        current = ""
        for word in raw_line.split():
            test = (current + " " + word).strip()
            if draw.textlength(test, font=font) <= max_width:
                current = test
            else:
                if current:
                    lines.append(current)
                current = word
        if current:
            lines.append(current)
    return lines


def draw_centered(draw, y, text, font):
    bbox = draw.textbbox((0, y), text, font=font)
    width = bbox[2] - bbox[0]
    x = max(0, (PRINTER_WIDTH - width) // 2)
    draw.text((x, y), text, font=font, fill="black")
    return y + (bbox[3] - bbox[1])


def create_qr_image(payload, size=306):
    qr = qrcode.QRCode(
        version=None,
        error_correction=qrcode.constants.ERROR_CORRECT_M,
        box_size=10,
        border=2,
    )
    qr.add_data(payload)
    qr.make(fit=True)
    img = qr.make_image(fill_color="black", back_color="white").convert("RGB")
    img = img.resize((size, size), Image.Resampling.NEAREST)
    canvas = Image.new("RGB", (PRINTER_WIDTH, size + 20), "white")
    canvas.paste(img, ((PRINTER_WIDTH - img.width) // 2, 10))
    return canvas


def create_label_image(base_url: str, key: str, display_name: str = "", subtitle: str = ""):
    qr_url = build_ice_url(base_url, key)
    img = Image.new("RGB", (PRINTER_WIDTH, 1800), "white")
    draw = ImageDraw.Draw(img)

    font_title = load_font(31, bold=True)
    font_sub = load_font(19, bold=True)
    font_normal = load_font(18)
    font_small = load_font(15)

    y = 12
    y = draw_centered(draw, y, "NOTFALL QR CODE", font_title)
    y += 6
    y = draw_centered(draw, y, "ICE / In Case of Emergency", font_sub)
    y += 10

    qr_img = create_qr_image(qr_url)
    img.paste(qr_img, (0, y))
    y += qr_img.height + 8

    if display_name.strip():
        y = draw_centered(draw, y, display_name.strip()[:38], font_sub)
        y += 6

    if subtitle.strip():
        for line in wrap_text(draw, subtitle.strip(), font_normal, PRINTER_WIDTH - 18):
            draw.text((9, y), line, font=font_normal, fill="black")
            y += 22
        y += 4

    draw.line((16, y, PRINTER_WIDTH - 16, y), fill="black", width=1)
    y += 8

    info = [
        "QR scannen fuer Notfalldaten.",
        "Daten online im ICE-Admin verwaltet.",
        "Key: " + key.strip(),
    ]
    for item in info:
        for line in wrap_text(draw, item, font_small, PRINTER_WIDTH - 18):
            draw.text((9, y), line, font=font_small, fill="black")
            y += 18

    return trim_image(img)


def print_image(im):
    if serial is None:
        raise RuntimeError("pyserial fehlt. Installiere es mit: pip install pyserial")

    if im.width > PRINTER_WIDTH:
        h = int(im.height * (PRINTER_WIDTH / im.width))
        im = im.resize((PRINTER_WIDTH, h))

    if im.width < PRINTER_WIDTH:
        padded = Image.new("RGB", (PRINTER_WIDTH, im.height), "white")
        padded.paste(im, ((PRINTER_WIDTH - im.width) // 2, 0))
        im = padded

    # Bewährte YHK/TEDi-Rasterlogik: 180 Grad drehen und invertiert senden.
    im = im.rotate(180)
    im = im.convert("1")
    im = ImageOps.invert(im.convert("L")).convert("1")

    buf = b"".join((
        b"\x1d\x76\x30\x00",
        struct.pack("2B", int(im.width / 8 % 256), int(im.width / 8 / 256)),
        struct.pack("2B", int(im.height % 256), int(im.height / 256)),
        im.tobytes(),
    ))

    with serial.Serial(COM_PORT, baudrate=BAUDRATE, timeout=2) as s:
        sleep(1)
        s.write(b"\x1b\x40")
        sleep(0.25)
        s.write(b"\x1d\x49\xf0\x19")
        sleep(0.25)
        s.write(buf)
        sleep(0.3)
        s.write(b"\x0a\x0a\x0a\x0a")
        sleep(0.2)


# ==============================
# GUI
# ==============================
root = tk.Tk()
root.title("ICE QR Printer - Server Sync")
root.geometry("560x430")
root.minsize(500, 390)

main = ttk.Frame(root, padding=14)
main.pack(fill="both", expand=True)

intro = (
    "Der Server ist führend: Gesundheitsdaten werden online im ICE-Admin gepflegt. "
    "Dieser Drucker druckt nur den QR-Link mit ICE-Key."
)
ttk.Label(main, text=intro, wraplength=520).pack(anchor="w", pady=(0, 12))

form = ttk.Frame(main)
form.pack(fill="x")


def add_entry(label, default=""):
    row = ttk.Frame(form)
    row.pack(fill="x", pady=4)
    ttk.Label(row, text=label, width=16).pack(side="left", anchor="w")
    var = tk.StringVar(value=default)
    entry = ttk.Entry(row, textvariable=var)
    entry.pack(side="left", fill="x", expand=True)
    return var


base_url_var = add_entry("Server URL", DEFAULT_BASE_URL)
key_var = add_entry("ICE Key", new_ice_key())
name_var = add_entry("Name Etikett", "")
subtitle_var = add_entry("Kurzinfo", "")

link_var = tk.StringVar(value=build_ice_url(base_url_var.get(), key_var.get()))


def update_link(*_):
    key = key_var.get().strip()
    base = base_url_var.get().strip()
    if key:
        link_var.set(build_ice_url(base, key))
    else:
        link_var.set("")


base_url_var.trace_add("write", update_link)
key_var.trace_add("write", update_link)

link_frame = ttk.LabelFrame(main, text="QR-Link")
link_frame.pack(fill="x", pady=12)
ttk.Label(link_frame, textvariable=link_var, wraplength=510).pack(anchor="w", padx=8, pady=8)


def validate_key():
    key = key_var.get().strip()
    if not key:
        messagebox.showwarning("Fehlt", "Bitte ICE-Key eintragen oder neuen Key erzeugen.")
        return None
    allowed = set("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-")
    if len(key) < 12 or len(key) > 96 or any(ch not in allowed for ch in key):
        messagebox.showwarning("Ungültiger Key", "Erlaubt: A-Z, a-z, 0-9, _ und -, Länge 12 bis 96 Zeichen.")
        return None
    return key


def generate_key():
    key_var.set(new_ice_key())


def copy_link():
    key = validate_key()
    if not key:
        return
    root.clipboard_clear()
    root.clipboard_append(build_ice_url(base_url_var.get(), key))
    messagebox.showinfo("Kopiert", "QR-Link wurde in die Zwischenablage kopiert.")


def open_public_link():
    key = validate_key()
    if key:
        webbrowser.open(build_ice_url(base_url_var.get(), key))


def open_admin_edit():
    key = validate_key()
    if key:
        webbrowser.open(build_admin_edit_url(base_url_var.get(), key))


def preview_label():
    key = validate_key()
    if not key:
        return
    img = create_label_image(base_url_var.get(), key, name_var.get(), subtitle_var.get())
    img.save(PREVIEW_FILE)
    messagebox.showinfo("Vorschau gespeichert", f"PNG gespeichert:\n{PREVIEW_FILE}")


def print_label():
    key = validate_key()
    if not key:
        return
    try:
        img = create_label_image(base_url_var.get(), key, name_var.get(), subtitle_var.get())
        print_image(img)
        messagebox.showinfo("Gedruckt", "ICE QR Etikett wurde gedruckt.")
    except Exception as exc:
        messagebox.showerror("Druckfehler", str(exc))


buttons = ttk.Frame(main)
buttons.pack(fill="x", pady=(4, 0))

ttk.Button(buttons, text="Neuen Key erzeugen", command=generate_key).pack(fill="x", pady=3)
ttk.Button(buttons, text="ICE-Link kopieren", command=copy_link).pack(fill="x", pady=3)
ttk.Button(buttons, text="Admin öffnen / Datensatz bearbeiten", command=open_admin_edit).pack(fill="x", pady=3)
ttk.Button(buttons, text="Öffentlichen QR-Link öffnen", command=open_public_link).pack(fill="x", pady=3)
ttk.Button(buttons, text="PNG-Vorschau speichern", command=preview_label).pack(fill="x", pady=3)
ttk.Button(buttons, text="NOTFALL QR CODE drucken", command=print_label).pack(fill="x", pady=6)

status = f"Drucker: {COM_PORT} / {BAUDRATE} Baud | Papierbreite: {PRINTER_WIDTH}px | Serverroute: /ice/"
ttk.Label(main, text=status).pack(anchor="w", pady=(8, 0))

root.mainloop()
