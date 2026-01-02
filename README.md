<div align="center">
  <img src="docs/logo.webp" alt="YtPilot Logo" width="200"/>
  
  # YtPilot
  
  ### 🚀 Powerful PHP backend wrapper for yt-dlp with automatic binary management
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
  [![PHPStan Level 6](https://img.shields.io/badge/PHPStan-Level%206-brightgreen)](https://phpstan.org/)
  [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
  
  [Installation](#-installation) • [Quick Start](#-quick-start) • [CLI Commands](#-cli-commands) • [Examples](#-examples) • [Other Platforms](#-other-platforms) • [API Reference](#-complete-api)
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
composer require b7s/ytpilot
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

### 9. Using Cookies for Authenticated Content

#### Load cookies from file (Netscape format)

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=PRIVATE_VIDEO_ID')
    ->cookies('/path/to/cookies.txt')
    ->best()
    ->download();
```

#### Extract cookies directly from browser

```php
// From Chrome
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=AGE_RESTRICTED_VIDEO')
    ->cookiesFromBrowser('chrome')
    ->best()
    ->download();

// From Firefox with specific profile
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=PREMIUM_VIDEO')
    ->cookiesFromBrowser('firefox', 'default-release')
    ->best()
    ->download();

// From Firefox with profile and container
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=VIDEO_ID')
    ->cookiesFromBrowser('firefox', 'default-release', 'Personal')
    ->best()
    ->download();
```

**How to export cookies to Netscape format:**

You can use browser extensions like:

- **Chrome/Edge:** [Get cookies.txt LOCALLY](https://chrome.google.com/webstore/detail/get-cookiestxt-locally/cclelndahbckbenkjhflpdbgdldlbecc)
- **Firefox:** [cookies.txt](https://addons.mozilla.org/en-US/firefox/addon/cookies-txt/)

**Supported browsers:** `brave`, `chrome`, `chromium`, `edge`, `firefox`, `opera`, `safari`, `vivaldi`, `whale`

---

### 10. Using Proxy for Downloads

#### Simple proxy configuration

```php
// HTTP proxy
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->httpProxy('proxy.example.com', 8080)
    ->best()
    ->download();

// SOCKS5 proxy
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->socks5Proxy('proxy.example.com', 1080)
    ->best()
    ->download();

// Full proxy URL (any protocol)
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->proxy('socks5://user:pass@proxy.example.com:1080')
    ->best()
    ->download();
```

#### Proxy with authentication

```php
use YtPilot\Enums\ProxyProtocol;

YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->proxyWithAuth(
        host: 'proxy.example.com',
        port: 8080,
        username: 'myuser',
        password: 'mypassword',
        protocol: ProxyProtocol::Http
    )
    ->best()
    ->download();
```

#### Using Tor for anonymous downloads

```php
// Default Tor port (9050)
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->torProxy()
    ->best()
    ->download();

// Tor Browser port (9150)
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->torProxy(9150)
    ->best()
    ->download();
```

#### Geo-verification proxy

Use a different proxy only for geo-verification while downloading through your main connection:

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=GEO_RESTRICTED_VIDEO')
    ->geoVerificationProxy('http://us-proxy.example.com:8080')
    ->best()
    ->download();
```

#### Force IPv4 or IPv6

```php
// Force IPv4 connections
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->forceIpv4()
    ->best()
    ->download();

// Force IPv6 connections
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->forceIpv6()
    ->best()
    ->download();
```

#### Bind to specific source address

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->sourceAddress('192.168.1.100')
    ->best()
    ->download();
```

**Supported proxy protocols:** `http`, `https`, `socks4`, `socks4a`, `socks5`, `socks5h`

> **Note:** `socks5h` resolves DNS through the proxy server, useful for accessing .onion sites through Tor.

---

## 🌐 Other Platforms

While YouTube is the primary focus, YtPilot supports downloading from many other platforms through yt-dlp. Just pass the URL and it works automatically for most platforms.

### Supported Platforms

YtPilot supports **1000+ websites** including:

> **Important:** Only video/audio content is downloaded. Text posts, images, captions, and comments are not included.

| Platform    | Example URL                     | Subtitles | Notes                   |
| ----------- | ------------------------------- | --------- | ----------------------- |
| Instagram   | `instagram.com/reel/...`        | ❌        | 🔒 Cookies recommended  |
| Twitter/X   | `twitter.com/user/status/...`   | ❌        | 🔓 Works without auth   |
| TikTok      | `tiktok.com/@user/video/...`    | ❌        | 🔓 Works without auth   |
| Facebook    | `facebook.com/watch?v=...`      | ⚠️        | 🔒 Cookies recommended  |
| Vimeo       | `vimeo.com/123456`              | ✅        | 🔐 Password for private |
| Twitch      | `twitch.tv/videos/...`          | ✅        | VODs and clips          |
| Reddit      | `reddit.com/r/.../comments/...` | ❌        | 🔓 Works without auth   |
| SoundCloud  | `soundcloud.com/artist/track`   | N/A       | Audio only              |
| Dailymotion | `dailymotion.com/video/...`     | ✅        | 🔓 Works without auth   |
| Bilibili    | `bilibili.com/video/...`        | ✅        | 🔐 Session for premium  |

### Basic Usage

For most platforms, just pass the URL:

```php
use YtPilot\YtPilot;

// Instagram
YtPilot::make()
    ->url('https://www.instagram.com/reel/ABC123/')
    ->cookiesFromBrowser('chrome')  // Recommended for Instagram
    ->download();

// Twitter/X
YtPilot::make()
    ->url('https://twitter.com/user/status/123456789')
    ->best()
    ->download();

// TikTok
YtPilot::make()
    ->url('https://www.tiktok.com/@user/video/123456789')
    ->download();

// Facebook
YtPilot::make()
    ->url('https://www.facebook.com/watch?v=123456789')
    ->cookiesFromBrowser('chrome')
    ->download();

// Reddit
YtPilot::make()
    ->url('https://www.reddit.com/r/subreddit/comments/abc123/title/')
    ->download();

// SoundCloud
YtPilot::make()
    ->url('https://soundcloud.com/artist/track-name')
    ->audioOnly()
    ->audioFormat('mp3')
    ->download();

// Twitch VOD
YtPilot::make()
    ->url('https://www.twitch.tv/videos/123456789')
    ->download();

// Vimeo with subtitles
YtPilot::make()
    ->url('https://vimeo.com/123456789')
    ->video()
    ->subtitles()
    ->subtitleLanguages(['en', 'pt'])
    ->download();
```

### Platform-Specific Configuration

Some platforms have optional configuration methods for advanced use cases:

#### Twitter/X API Selection

```php
use YtPilot\Enums\TwitterApi;

// Using syndication API (default, no auth required)
YtPilot::make()
    ->url('https://twitter.com/user/status/123456789')
    ->configTwitter(TwitterApi::Syndication)
    ->download();

// Using GraphQL API (may require cookies for some content)
YtPilot::make()
    ->url('https://x.com/user/status/123456789')
    ->configTwitter(TwitterApi::GraphQL)
    ->cookiesFromBrowser('chrome')
    ->download();
```

#### TikTok API Selection

```php
use YtPilot\Enums\TikTokApi;

// Using web API (default)
YtPilot::make()
    ->url('https://www.tiktok.com/@user/video/123456789')
    ->configTikTok(TikTokApi::Web)
    ->download();

// Using app API
YtPilot::make()
    ->url('https://vm.tiktok.com/ABC123/')
    ->configTikTok(TikTokApi::App)
    ->download();
```

#### Vimeo Password-Protected Videos

```php
YtPilot::make()
    ->url('https://vimeo.com/123456789')
    ->configVimeo('video_password')
    ->download();
```

#### Bilibili Premium Content

```php
YtPilot::make()
    ->url('https://www.bilibili.com/video/BV1xx411c7mD')
    ->configBilibili('your_sess_data_cookie')
    ->download();
```

### Generic Authentication

For platforms requiring username/password:

```php
YtPilot::make()
    ->url('https://example.com/video/123')
    ->withCredentials('username', 'password')
    ->download();
```

### Custom Extractor Arguments

For advanced use cases with any extractor:

```php
YtPilot::make()
    ->url('https://example.com/video/123')
    ->extractorArgs('youtube', [
        'player_client' => 'android',
        'skip' => 'dash',
    ])
    ->download();
```

### Notes

- **Cookies:** Some platforms (Instagram, Facebook) require authentication via cookies
- **Rate Limiting:** Add delays between downloads if you experience issues
- **Terms of Service:** Respect each platform's terms when downloading content
- **Subtitles:** Only some platforms support subtitles (YouTube, Vimeo, Twitch, Dailymotion, Bilibili). Social media platforms like Instagram, Twitter, TikTok, and Reddit typically don't have subtitle support

---

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

#### Cookie Options

| Method                                         | Description                     | Example                             |
| ---------------------------------------------- | ------------------------------- | ----------------------------------- |
| `cookies(string)`                              | Load cookies from Netscape file | `->cookies('/path/to/cookies.txt')` |
| `cookiesFromBrowser(string, ?string, ?string)` | Extract cookies from browser    | `->cookiesFromBrowser('chrome')`    |
| `noCookies()`                                  | Explicitly disable cookies      | `->noCookies()`                     |

**Supported browsers:** `brave`, `chrome`, `chromium`, `edge`, `firefox`, `opera`, `safari`, `vivaldi`, `whale`

**Cookie use cases:**

- Access age-restricted content
- Download private/unlisted videos (with authenticated account)
- Access premium content (YouTube Premium, etc.)
- Bypass censorship/geo-restrictions

#### Proxy & Network Options

| Method                                                      | Description                       | Example                                           |
| ----------------------------------------------------------- | --------------------------------- | ------------------------------------------------- |
| `proxy(string)`                                             | Set proxy URL (any protocol)      | `->proxy('http://proxy:8080')`                    |
| `proxyWithAuth(string, int, string, string, ProxyProtocol)` | Proxy with authentication         | `->proxyWithAuth('host', 8080, 'user', 'pass')`   |
| `httpProxy(string, int)`                                    | HTTP proxy                        | `->httpProxy('proxy.example.com', 8080)`          |
| `httpsProxy(string, int)`                                   | HTTPS proxy                       | `->httpsProxy('proxy.example.com', 443)`          |
| `socks4Proxy(string, int)`                                  | SOCKS4 proxy                      | `->socks4Proxy('proxy.example.com', 1080)`        |
| `socks5Proxy(string, int)`                                  | SOCKS5 proxy                      | `->socks5Proxy('proxy.example.com', 1080)`        |
| `socks5hProxy(string, int)`                                 | SOCKS5 with remote DNS resolution | `->socks5hProxy('proxy.example.com', 1080)`       |
| `torProxy(int)`                                             | Tor proxy (default port 9050)     | `->torProxy()` or `->torProxy(9150)`              |
| `noProxy()`                                                 | Explicitly disable proxy          | `->noProxy()`                                     |
| `geoVerificationProxy(string)`                              | Proxy for geo-verification only   | `->geoVerificationProxy('http://geo-proxy:8080')` |
| `sourceAddress(string)`                                     | Bind to specific IP address       | `->sourceAddress('192.168.1.100')`                |
| `forceIpv4()`                                               | Force IPv4 connections            | `->forceIpv4()`                                   |
| `forceIpv6()`                                               | Force IPv6 connections            | `->forceIpv6()`                                   |

**Supported proxy protocols:** `http`, `https`, `socks4`, `socks4a`, `socks5`, `socks5h`

**Proxy use cases:**

- Bypass censorship/geo-restrictions
- Avoid IP bans and rate limiting
- Access content from specific regions
- Route traffic through Tor for anonymity

#### Platform Configuration

| Method                            | Description                | Example                                    |
| --------------------------------- | -------------------------- | ------------------------------------------ |
| `configTwitter(TwitterApi)`       | Configure Twitter API      | `->configTwitter(TwitterApi::Syndication)` |
| `configTikTok(TikTokApi)`         | Configure TikTok API       | `->configTikTok(TikTokApi::Web)`           |
| `configVimeo(string)`             | Vimeo password-protected   | `->configVimeo('password')`                |
| `configBilibili(string)`          | Bilibili session data      | `->configBilibili('sess_data')`            |
| `withCredentials(string, string)` | Set username/password      | `->withCredentials('user', 'pass')`        |
| `extractorArgs(string, array)`    | Custom extractor arguments | `->extractorArgs('youtube', [...])`        |

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

---

## 📊 Progress Callbacks

Track download and conversion progress in real-time:

### Download Progress

```php
YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->onDownloading(function (int $percentage, float $downloaded, float $total): void {
        echo "\rDownloading: {$percentage}% ";
    })
    ->download();
```

### Conversion Progress

```php
YtPilot::make()
    ->onConverting(function (int $percentage, float $current, float $duration): void {
        $currentTime = gmdate('H:i:s', (int) $current);
        $totalTime = gmdate('H:i:s', (int) $duration);
        echo "\rConverting: {$percentage}% ({$currentTime} / {$totalTime}) ";
    })
    ->convertVideoToMp4('input.webm', 'output.mp4');
```

**Callback Parameters:**

- `$percentage` - Progress percentage (0-100)
- `$downloaded` / `$current` - Current bytes/seconds processed
- `$total` / `$duration` - Total bytes/seconds to process

---

## 🔄 Video & Audio Conversion

Convert media files between formats using the project's ffmpeg binary. All conversion methods now support **smart defaults** for seamless workflow integration.

### Video Conversion Methods

| Method                                                                                  | Description                |
| --------------------------------------------------------------------------------------- | -------------------------- |
| `convertVideoTo(?string $input, ?string $output, string $format, bool $deleteOriginal)` | Convert to custom format   |
| `convertVideoToMp4(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to MP4 (H.264/AAC) |
| `convertVideoToMkv(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to MKV (copy)      |
| `convertVideoToWebm(?string $input, ?string $output, bool $deleteOriginal)`             | Convert to WebM (VP9/Opus) |
| `convertVideoToAvi(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to AVI (H.264/MP3) |

### Audio Conversion Methods

| Method                                                                                  | Description                |
| --------------------------------------------------------------------------------------- | -------------------------- |
| `convertAudioTo(?string $input, ?string $output, string $format, bool $deleteOriginal)` | Convert to custom format   |
| `convertAudioToMp3(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to MP3 (320kbps)   |
| `convertAudioToM4a(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to M4A (AAC 192k)  |
| `convertAudioToOpus(?string $input, ?string $output, bool $deleteOriginal)`             | Convert to Opus (128kbps)  |
| `convertAudioToOgg(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to Ogg Vorbis      |
| `convertAudioToWav(?string $input, ?string $output, bool $deleteOriginal)`              | Convert to WAV (PCM)       |
| `convertAudioToFlac(?string $input, ?string $output, bool $deleteOriginal)`             | Convert to FLAC (lossless) |

### Smart Conversion Features

**All parameters are optional with intelligent defaults:**

- `$input` - If `null`, uses the last downloaded video/audio file automatically
- `$output` - If `null`, saves to the same directory with the correct extension
- `$deleteOriginal` - Default `true`, automatically removes the original file after successful conversion

### Conversion Examples

**1. Download and convert (simplest way):**

```php
$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video()
    ->onDownloading(fn($p) => sendToSomeWebhook($p))
    ->onConverting(fn($p) => sendToOtherWebhook($p));

$ytpilot->download();

// No parameters needed! Uses downloaded video, auto-names output, deletes original
$ytpilot->convertVideoToMp4();
```

**2. Download and convert (keep original):**

```php
$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->audioOnly();

$ytpilot->download();

// Keep the original file
$ytpilot->convertAudioToMp3(deleteOriginal: false);
```

**3. Download with custom output path:**

```php
$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video();

$ytpilot->download();

// Custom output path, keep original
$ytpilot->convertVideoToMp4(
    outputPath: '/tmp/my-video.mp4',
    deleteOriginal: false
);
```

**4. Convert existing files (explicit input):**

```php
$ytpilot = YtPilot::make();

// Auto output name, keep original
$ytpilot->convertVideoToWebm('video.mp4', deleteOriginal: false);

// Custom output name, delete original
$ytpilot->convertVideoToMkv('video.mp4', 'custom-name.mkv');

// Convert audio formats
$ytpilot->convertAudioToMp3('audio.m4a', deleteOriginal: false);
$ytpilot->convertAudioToFlac('audio.wav', 'high-quality.flac');
```

**5. Custom format with progress tracking:**

```php
YtPilot::make()
    ->onConverting(function (int $percentage, float $current, float $duration): void {
        $time = gmdate('H:i:s', (int) $current);
        $total = gmdate('H:i:s', (int) $duration);
        echo "\rProgress: {$percentage}% ({$time} / {$total}) ";
    })
    ->convertVideoTo('input.avi', format: 'mp4', deleteOriginal: false);
```

**6. Batch conversion workflow:**

```php
$ytpilot = YtPilot::make()
    ->url('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
    ->video();

$result = $ytpilot->download();

if ($result->success && $result->videoPath) {
    // Create multiple formats from the same source
    $ytpilot->convertVideoToMp4(deleteOriginal: false);  // video.mp4
    $ytpilot->convertVideoToWebm(deleteOriginal: false); // video.webm
    $ytpilot->convertVideoToMkv(deleteOriginal: true);   // video.mkv (deletes original)
}
```

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
