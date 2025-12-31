<div align="center">
  <img src="docs/logo.webp" alt="YtPilot Logo" width="200"/>
  
  # YtPilot
  
  ### 🚀 Powerful PHP backend wrapper for yt-dlp with automatic binary management
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
  [![PHPStan Level 6](https://img.shields.io/badge/PHPStan-Level%206-brightgreen)](https://phpstan.org/)
  [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
  
  [Installation](#-installation) • [Quick Start](#-quick-start) • [CLI Commands](#-cli-commands) • [Examples](#-examples) • [API Reference](#-complete-api)
</div>

---

## ✨ Features

- 🎯 **Fluent API** - Laravel-inspired chainable interface
- 📦 **Automatic Management** - Downloads and manages yt-dlp, ffmpeg, and ffprobe automatically
- 🔍 **Media Discovery** - Query available formats, resolutions, codecs, and subtitles
- 🎬 **Smart Presets** - Cinema, mobile, and archive quality presets
- 🌍 **Cross-Platform** - Linux, macOS, and Windows support
- 🎯 **Multi-Target** - Download video, audio, subtitles, metadata, and thumbnails in one command
- 🔒 **Type-Safe** - Full PHP 8.3+ type hints and PHPStan level 6 compliant
- 🛠️ **Complete CLI** - Command-line tools for installation and diagnostics

---

## 📦 Installation

```bash
composer require ytpilot/ytpilot
```

### Install Binaries

After installing the package, install the required binaries:

```bash
vendor/bin/ytpilot install
```

This command will:

- ✅ Download the latest version of **yt-dlp**
- ✅ Download **ffmpeg** and **ffprobe** (if needed)
- ✅ Configure everything automatically

---

## 🚀 Quick Start

### Simple Download

```php
use YtPilot\YtPilot;

// Download video with best quality
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->best()
    ->download();
```

### Extract Audio Only

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->audioOnly()
    ->audioFormat('mp3')
    ->audioQuality('320K')
    ->download();
```

### Complete Download

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->audio()
    ->subtitles()
    ->metadata()
    ->thumbnail()
    ->download();
```

---

## 🛠️ CLI Commands

### `install` - Install Binaries

Automatically installs yt-dlp and ffmpeg:

```bash
vendor/bin/ytpilot install
```

**Options:**

- `--skip-ffmpeg` - Skip ffmpeg installation
- `--force` or `-f` - Force reinstallation even if already exists

**Examples:**

```bash
# Complete installation
vendor/bin/ytpilot install

# Install only yt-dlp
vendor/bin/ytpilot install --skip-ffmpeg

# Force reinstallation
vendor/bin/ytpilot install --force
```

---

### `update` - Update Binaries

Updates binaries to the latest versions:

```bash
vendor/bin/ytpilot update
```

**Options:**

- `--skip-ffmpeg` - Skip ffmpeg update

**Examples:**

```bash
# Update everything
vendor/bin/ytpilot update

# Update only yt-dlp
vendor/bin/ytpilot update --skip-ffmpeg
```

---

### `version` - Check Versions

Displays YtPilot and installed binary versions:

```bash
vendor/bin/ytpilot version
```

**Output:**

```
YtPilot Version Information
===========================

 ---------- -----------
  YtPilot    1.0.0
  PHP        8.3.0
  Platform   linux-x64
 ---------- -----------

Binary Versions
---------------

 --------- ------------ --------------------------------
  Binary    Version      Path
 --------- ------------ --------------------------------
  yt-dlp    2024.12.23   /path/to/.ytpilot/bin/yt-dlp
  ffmpeg    7.1          /usr/bin/ffmpeg
  ffprobe   7.1          /usr/bin/ffprobe
 --------- ------------ --------------------------------
```

---

### `doctor` - Complete Diagnostics

Checks YtPilot installation and configuration:

```bash
vendor/bin/ytpilot doctor
```

**What it checks:**

- ✅ System information (OS, architecture, PHP)
- ✅ Binary status (installed, paths, source)
- ✅ Directory permissions
- ✅ Binary versions
- ✅ Current configuration

**Example output:**

```
YtPilot Doctor
==============

System Information
------------------
  Operating System   linux
  Architecture       x64
  Platform ID        linux-x64
  PHP Version        8.3.0
  Bin Directory      /path/to/.ytpilot/bin

Binary Status
-------------
  Binary    Status         Source              Path
  yt-dlp    ✓ Installed    Local (downloaded)  /path/to/.ytpilot/bin/yt-dlp
  ffmpeg    ✓ Installed    System (global)     /usr/bin/ffmpeg
  ffprobe   ✓ Installed    System (global)     /usr/bin/ffprobe

✓ All checks passed! YtPilot is ready to use.
```

---

## 📖 Examples

### 1. Query Available Formats

```php
$pilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

// Get all formats
$formats = $pilot->getAvailableFormats();
foreach ($formats as $format) {
    echo "ID: {$format->id}, Resolution: {$format->resolution}, Codec: {$format->vcodec}\n";
}

// Get available resolutions
$resolutions = $pilot->getAvailableResolutions();
echo "Resolutions: " . implode(', ', $resolutions) . "\n";

// Get video codecs
$codecs = $pilot->getAvailableVideoCodecs();
echo "Codecs: " . implode(', ', $codecs) . "\n";

// Get available subtitles
$subtitles = $pilot->getAvailableSubtitles();
echo "Languages: " . implode(', ', $subtitles->getLanguages()) . "\n";
```

---

### 2. Use Quality Presets

#### 🎬 Cinema (High Quality)

Perfect for large screens, prioritizes quality:

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->cinema()  // 1080p+, AV1/VP9/AVC, best audio
    ->download();
```

#### 📱 Mobile (Reduced Size)

Perfect for mobile devices, prioritizes size:

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->mobile()  // 720p max, AVC, 128k audio
    ->download();
```

#### 🗄️ Archive (Maximum Quality)

Perfect for long-term archiving:

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->archive()  // Best available codec (AV1/VP9)
    ->download();
```

---

### 3. Download with Subtitles

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->audio()
    ->subtitles()
    ->subtitleLanguages(['en', 'es', 'fr'])
    ->subtitleAsSrt()  // or ->subtitleFormat('srt')
    ->download();
```

---

### 4. Custom Format

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->resolution('1080p')
    ->fps(60)
    ->videoCodec('av01')
    ->audioCodec('opus')
    ->download();
```

---

### 5. Custom Output Directory

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->outputPath('./downloads')
    ->output('%(title)s-%(id)s.%(ext)s')
    ->download();
```

---

### 6. Handle Download Result

```php
$result = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->audio()
    ->subtitles()
    ->metadata()
    ->thumbnail()
    ->download();

if ($result->success) {
    echo "✓ Download completed!\n";
    echo "Video: {$result->videoPath}\n";
    echo "Audio: {$result->audioPath}\n";
    echo "Thumbnail: {$result->thumbnailPath}\n";
    echo "Metadata: {$result->metadataPath}\n";
    echo "Subtitles: " . implode(', ', $result->subtitlePaths) . "\n";
    echo "Total files: " . count($result->downloadedFiles) . "\n";
} else {
    echo "✗ Download failed\n";
    echo "Error: {$result->output}\n";
}
```

---

### 7. Use Custom Binaries

```php
YtPilot::make()
    ->withYtDlpPath('/usr/local/bin/yt-dlp')
    ->withFfmpegPath('/usr/local/bin/ffmpeg')
    ->withFfprobePath('/usr/local/bin/ffprobe')
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->best()
    ->download();
```

---

### 8. Custom Timeout

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->timeout(600)  // 10 minutes
    ->best()
    ->download();
```

---

## 🎯 Complete API

### Target Methods (Accumulative)

Define **what** will be downloaded. Can be combined:

| Method            | Description                  |
| ----------------- | ---------------------------- |
| `video()`         | Download video               |
| `audio()`         | Download audio               |
| `subtitles()`     | Download manual subtitles    |
| `autoSubtitles()` | Download automatic subtitles |
| `metadata()`      | Download JSON metadata       |
| `thumbnail()`     | Download thumbnail           |

---

### Format Methods (Chainable)

Control **how** the download will be performed:

#### Format Selection

| Method               | Description         | Example                           |
| -------------------- | ------------------- | --------------------------------- |
| `format(string)`     | Custom selector     | `->format('bestvideo+bestaudio')` |
| `best()`             | Best quality        | `->best()`                        |
| `worst()`            | Worst quality       | `->worst()`                       |
| `resolution(string)` | Specific resolution | `->resolution('1080p')`           |
| `fps(int)`           | Maximum FPS         | `->fps(60)`                       |
| `videoCodec(string)` | Video codec         | `->videoCodec('av01')`            |
| `audioCodec(string)` | Audio codec         | `->audioCodec('opus')`            |
| `container(string)`  | Container format    | `->container('mp4')`              |
| `hdr()`              | HDR content         | `->hdr()`                         |
| `sdr()`              | SDR content         | `->sdr()`                         |

#### Smart Presets

| Method      | Description                    | Format              |
| ----------- | ------------------------------ | ------------------- |
| `cinema()`  | High quality for large screens | 1080p+, AV1/VP9/AVC |
| `mobile()`  | Reduced size for mobile        | 720p max, AVC, 128k |
| `archive()` | Maximum quality for archiving  | AV1/VP9 preferred   |

#### Audio Options

| Method                 | Description        | Example                  |
| ---------------------- | ------------------ | ------------------------ |
| `audioOnly()`          | Extract audio only | `->audioOnly()`          |
| `audioFormat(string)`  | Audio format       | `->audioFormat('mp3')`   |
| `audioQuality(string)` | Audio quality      | `->audioQuality('320K')` |

#### Subtitle Options

| Method                     | Description               | Example                             |
| -------------------------- | ------------------------- | ----------------------------------- |
| `subtitleLanguages(array)` | Subtitle languages        | `->subtitleLanguages(['en', 'es'])` |
| `subtitleFormat(string)`   | Subtitle format (custom)  | `->subtitleFormat('srt')`           |
| `subtitleAsSrt()`          | Subtitle as SRT format    | `->subtitleAsSrt()`                 |
| `subtitleAsVtt()`          | Subtitle as WebVTT format | `->subtitleAsVtt()`                 |
| `subtitleAsAss()`          | Subtitle as ASS format    | `->subtitleAsAss()`                 |
| `subtitleAsSsa()`          | Subtitle as SSA format    | `->subtitleAsSsa()`                 |
| `subtitleAsLrc()`          | Subtitle as LRC format    | `->subtitleAsLrc()`                 |
| `subtitleAsTtml()`         | Subtitle as TTML format   | `->subtitleAsTtml()`                |
| `subtitleAsJson3()`        | Subtitle as JSON3 format  | `->subtitleAsJson3()`               |

#### Output Options

| Method               | Description              | Example                         |
| -------------------- | ------------------------ | ------------------------------- |
| `output(string)`     | Filename template        | `->output('%(title)s.%(ext)s')` |
| `outputPath(string)` | Output directory         | `->outputPath('./downloads')`   |
| `overwrite()`        | Overwrite existing files | `->overwrite()`                 |
| `skipDownload()`     | Skip actual download     | `->skipDownload()`              |
| `simulate()`         | Simulate download        | `->simulate()`                  |

> **Note:** If no `outputPath()` is specified, files are downloaded to the current working directory or the path defined in `config/ytpilot.php` under `download_path`.

#### Binary Management

| Method                    | Description                             | Example                                 |
| ------------------------- | --------------------------------------- | --------------------------------------- |
| `ensureInstalled()`       | Ensure binaries installed (auto-called) | `->ensureInstalled()`                   |
| `withYtDlpPath(string)`   | Custom yt-dlp path                      | `->withYtDlpPath('/usr/bin/yt-dlp')`    |
| `withFfmpegPath(string)`  | Custom ffmpeg path                      | `->withFfmpegPath('/usr/bin/ffmpeg')`   |
| `withFfprobePath(string)` | Custom ffprobe path                     | `->withFfprobePath('/usr/bin/ffprobe')` |
| `timeout(int)`            | Timeout in seconds                      | `->timeout(300)`                        |

> **Note:** `ensureInstalled()` is automatically called when using `YtPilot::make()`, so you don't need to call it manually unless you want to force a re-check.

---

### Query Methods (Non-Chainable)

Execute immediately and return data:

| Method                        | Return         | Description              |
| ----------------------------- | -------------- | ------------------------ |
| `getAvailableFormats()`       | `FormatItem[]` | All available formats    |
| `getAvailableResolutions()`   | `string[]`     | Available resolutions    |
| `getAvailableFrameRates()`    | `int[]`        | Available FPS            |
| `getAvailableVideoCodecs()`   | `string[]`     | Video codecs             |
| `getAvailableAudioCodecs()`   | `string[]`     | Audio codecs             |
| `getAvailableContainers()`    | `string[]`     | Container formats        |
| `getAvailableDynamicRanges()` | `string[]`     | Dynamic ranges (HDR/SDR) |
| `getAvailableSubtitles()`     | `SubtitleList` | Available subtitles      |

---

### Execution Method

| Method       | Return           | Description          |
| ------------ | ---------------- | -------------------- |
| `download()` | `DownloadResult` | Execute the download |

---

## ⚙️ Configuration

Create a `config/ytpilot.php` file:

```php
<?php

return [
    // Directory where binaries will be stored
    'bin_path' => '.ytpilot/bin',

    // yt-dlp configuration
    'yt_dlp' => [
        'path' => null, // null = auto-detect
    ],

    // ffmpeg configuration
    'ffmpeg' => [
        'path' => null,        // null = auto-detect
        'probe_path' => null,  // null = auto-detect
        'prefer_global' => true, // Prefer system binaries
        'enabled' => true,     // Enable ffmpeg
    ],

    // Default timeout in seconds
    'timeout' => 300,

    // Default download directory (null = current working directory)
    'download_path' => null,
];
```

### Download Location

By default, files are downloaded to the **current working directory** (where your PHP script is executed).

You can change this in three ways:

1. **Set in configuration file:**

```php
// config/ytpilot.php
return [
    'download_path' => './downloads',
    // ...
];
```

2. **Set per download:**

```php
YtPilot::make()
    ->url($url)
    ->outputPath('./my-videos')
    ->download();
```

3. **Set custom filename template:**

```php
YtPilot::make()
    ->url($url)
    ->outputPath('./downloads')
    ->output('%(title)s-%(id)s.%(ext)s')
    ->download();
```

---

## 🔍 Binary Resolution Priority

For all binaries (yt-dlp, ffmpeg, ffprobe), the resolution order is:

1. ✅ **Runtime override** via fluent API (`withYtDlpPath()`, etc.)
2. ✅ **Path defined** in configuration file
3. ✅ **System PATH** (if `prefer_global = true`)
4. ✅ **Automatic download** to local directory
5. ❌ **Exception** if all options fail

---

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Internet connection
- PHP Extensions:
  - `curl` or `allow_url_fopen` (for downloads)
  - `zip` (for ffmpeg extraction on Windows/macOS)

---

## 🤝 Contributing

Contributions are welcome! Feel free to:

- 🐛 Report bugs
- 💡 Suggest new features
- 🔧 Submit pull requests
- 📖 Improve documentation

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 🙏 Credits

- Built on top of [yt-dlp](https://github.com/yt-dlp/yt-dlp)
- Uses [Symfony Process](https://symfony.com/doc/current/components/process.html)
- Uses [Symfony Console](https://symfony.com/doc/current/components/console.html)

---

<div align="center">
  
  **Made by [me](https://github.com/b7s) with ❤️ for the PHP community**
  
  [⬆ Back to top](#ytpilot)
  
</div>
