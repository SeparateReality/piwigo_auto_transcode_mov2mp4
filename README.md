# CXO Auto Transcode MOV for Piwigo

Automatically converts **.mov** uploads and api uploads to browser-friendly **.mp4** and
offers a one-click bulk job for all legacy MOV files – no user side fiddling required.  
Works from Piwigo 11 upwards (hope so. Using v15).

---

## ATTENTION: No support. Its just to get you started.

---

## Features
| ✓ | Feature | Notes |
|---|---------|-------|
| 🚀 | **Instant transcoding on upload** | API, HTML form & mobile apps |
| 🔥 | *Copy-mux* if already H.264/HEVC | zero-loss, lightning fast |
| 🗑️ | Deletes the source *.mov* after success | keeps storage tidy |
| 🛠 | **Bulk converter** in the admin UI | converts every remaining MOV in one go |
| 🔍 | Live log viewer (auto-refresh) | plus “Clear Log” button |
| 🛡 | Low-priority FFmpeg (`nice 10`) | doesn’t hog your CPU |
| ♻️ | Compatible with Shared-Hosting | no root required, just PHP exec() access |

---

## Requirements
* **Piwigo 11+** (Has Settings header supported)  
* **FFmpeg 6.0+** (incl. `ffprobe`) in `$PATH`
* PHP `exec()` / `shell_exec()` **enabled**

Optionally:
* `libx264` / `libx265` if you expect non-H.264 MOVs
* Writable `plugins/CXO_AutoTranscode/cxo_transcode.log` for logging

---

## Installation

```bash
cd piwigo/plugins
git clone https://github.com/yourname/cxoAutoTranscode.git
```

1.	Log in to Administration → Plugins.
2.	Click Activate on CXO Auto Transcode MOV.
3.	Use the orange Settings button to open the bulk page.

Every future .mov upload is now converted transparently.

⸻

## Usage

### Automatic on upload

Upload videos exactly like photos—the plugin hooks in, creates an .mp4, updates the database, and deletes the original MOV.

### Bulk-convert legacy files

Open **Administration → Plugins → CXO MOV Transcoder**.
The page shows:
	•	Number of remaining MOVs
	•	**Convert legacy MOV files** button
	•	Live log that refreshes every 5 seconds

### Clear the log

Press **Clear Log** on the same page—handy after debugging.

⸻

## Configuration

Edit main.inc.php if you need to tweak anything:

| Setting              | Where                       | Default                      |
|----------------------|-----------------------------|------------------------------|
| FFmpeg binary path   | `$ffmpeg` strings           | `/usr/bin/ffmpeg`            |
| Nice priority        | `nice -n10`                 | `10`                         |
| CRF / preset         | transcode branch parameters | `-crf 22 -preset medium`     |
| Log file location    | `CXO_ATM_LOG` constant      | `plugins/.../cxo_transcode.log` |

⸻

## Troubleshooting

	•	MOV not converted? Check the log—codec unsupported or FFmpeg not in $PATH.
	•	Log stays empty? Verify PHP can write to the plugin directory.
	•	White admin page? Using Piwigo < 11? Uncomment the fallback get_admin_plugin_menu_links block.

⸻

## Roadmap (probably never happens)

	•	HLS/DASH output for very large videos
	•	Queue table + cron worker instead of synchronous loops
	•	Optional thumbnail generator for themes without VideoJS

⸻

## License

MIT — see LICENSE.

⸻

## Credits

	•	Code & idea — cxo
	•	Powered by FFmpeg and the awesome Piwigo community
