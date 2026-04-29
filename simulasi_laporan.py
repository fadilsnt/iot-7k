import requests
import random
import time
from datetime import datetime, timedelta

# URL endpoint API
API_URL_BULK = "http://localhost:8000/api/machine-readings/bulk"

def run_simulation():
    print("=== Simulasi Data IOT (SUPER CEPAT) ===")
    
    # 1. Konfigurasi Range Waktu
    print("\nMasukkan range waktu simulasi (Format: YYYY-MM-DD HH:MM)")
    start_str = input("Mulai   (default 2026-01-26 07:00): ") or "2026-01-26 07:00"
    end_str = input("Selesai (default 2026-02-01 07:00): ") or "2026-02-01 07:00"
    
    try:
        start_dt = datetime.strptime(start_str, "%Y-%m-%d %H:%M")
        end_dt = datetime.strptime(end_str, "%Y-%m-%d %H:%M")
    except ValueError:
        print("Format tanggal salah! Gunakan YYYY-MM-DD HH:MM")
        return

    # 2. Konfigurasi Mesin
    jumlah_mesin = int(input("Jumlah mesin yang akan disimulasikan (default 22): ") or "22")
    interval_detik = float(input("Interval pengiriman data (detik) (default 300): ") or "300")
    jam_operasional = float(input("Jam operasional per hari (default 4): ") or "4")

    # Ambil jam mulai sebagai patokan (misal jam 07:00)
    start_hour = start_dt.hour
    start_minute = start_dt.minute

    # Inisialisasi HM awal
    machine_state = {}
    for i in range(1, jumlah_mesin + 1):
        machine_id = f"MESIN-{i:02d}"
        machine_state[machine_id] = {"hm": round(random.uniform(100.0, 500.0), 2)}

    print(f"\nMemproses simulasi ({jam_operasional} jam/hari)...")
    current_dt = start_dt
    session = requests.Session()
    total_records = 0
    all_payloads = []

    while current_dt <= end_dt:
        # Cek apakah jam sekarang masuk dalam jam operasional hari ini
        # Kita hitung selisih jam dari jam mulai hari ini
        ref_start_today = current_dt.replace(hour=start_hour, minute=start_minute, second=0, microsecond=0)
        
        # Jika current_dt < ref_start, berarti ini sisa hari sebelumnya atau sebelum jam mulai
        if current_dt < ref_start_today:
            ref_start_today -= timedelta(days=1)
            
        diff_hours = (current_dt - ref_start_today).total_seconds() / 3600.0
        
        # Hanya kirim data jika masih dalam window jam operasional
        if 0 <= diff_hours < jam_operasional:
            timestamp_str = current_dt.isoformat()
            
            for i in range(1, jumlah_mesin + 1):
                machine_id = f"MESIN-{i:02d}"
                
                # Simulasi mesin jalan/mati (85% jalan)
                is_running = random.random() < 0.85
                amp = round(random.uniform(14.0, 24.0), 2) if is_running else 0
                
                # Update HM jika mesin jalan
                # 1 jam = 3600 detik. Maka per interval: interval_detik / 3600 jam
                if is_running:
                    hm_increment = interval_detik / 3600.0
                    machine_state[machine_id]["hm"] += hm_increment
                
                all_payloads.append({
                    "machine_id": machine_id,
                    "amp": amp,
                    "hm": round(machine_state[machine_id]["hm"], 2),
                    "temp": round(random.uniform(55.0, 85.0), 2),
                    "moist": round(random.uniform(15.0, 25.0), 2),
                    "timestamp": timestamp_str
                })
                total_records += 1

            # Kirim per batch (misal per 1000 records) agar tidak timeout tapi tetap cepat
            if len(all_payloads) >= 1000:
                try:
                    resp = session.post(API_URL_BULK, json={"data": all_payloads}, timeout=30)
                    if resp.status_code != 200 and resp.status_code != 201:
                        print(f"[{current_dt}] Gagal kirim batch: {resp.status_code} - {resp.text}")
                    else:
                        print(f"> Terkirim {total_records} data...")
                    all_payloads = []
                except Exception as e:
                    print(f"Error saat batch send: {e}")

        # Maju ke interval berikutnya
        current_dt += timedelta(seconds=interval_detik)
        
        # Cetak progress setiap 1 hari (simulasi)
        if current_dt.hour == 0 and current_dt.minute == 0:
            print(f"> Progress: Mencapai tanggal {current_dt.date()}...")

    # Kirim sisa data
    if all_payloads:
        try:
            resp = session.post(API_URL_BULK, json={"data": all_payloads}, timeout=30)
            if resp.status_code != 200 and resp.status_code != 201:
                print(f"Gagal kirim sisa data: {resp.status_code} - {resp.text}")
            else:
                print(f"> Terkirim sisa {len(all_payloads)} data.")
        except Exception as e:
            print(f"Error sisa data: {e}")

    print(f"\n✅ Selesai! Total {total_records} data berhasil disimulasikan.")
    print("Silakan cek dashboard atau download laporan sekarang.")

if __name__ == "__main__":
    run_simulation()
