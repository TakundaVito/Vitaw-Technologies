"""Bundle first-party assets and fingerprint references. No external dependencies."""
from pathlib import Path
import hashlib, re, json, gzip

root = Path(__file__).resolve().parents[1]
public = root / 'public'
css = (root / 'src/fonts.css').read_text(encoding='utf-8') + '\n'
css += (public / 'style.css').read_text(encoding='utf-8') + '\n'
css += (public / 'slideshow.css').read_text(encoding='utf-8')
css += '\n' + (public / 'mobile.css').read_text(encoding='utf-8')
css = re.sub(r'\.(?:geometry-stage|cross|c1|c2|axis|axis-x|axis-y|model-placeholder|model-label|stage-bottom)(?:[^{}]*)\{[^{}]*\}', '', css)
js = '(()=>{\n' + (public / 'app.js').read_text(encoding='utf-8') + '\n})();\n'
js += (public / 'slideshow.js').read_text(encoding='utf-8')
assert '@import' not in css and ':root{--navy:' in css
names = {}
for ext, content in [('css', css), ('js', js)]:
    name = f'site.{hashlib.sha256(content.encode()).hexdigest()[:12]}.{ext}'
    (public / name).write_text(content, encoding='utf-8')
    names[ext] = name

pages = sorted(public.rglob('*.html')) + [public / 'entry.php', public / 'listing.php']
for path in pages:
    name = path.relative_to(public).as_posix()
    html = path.read_text(encoding='utf-8')
    html = re.sub(r'<link rel="stylesheet" href="/(?:site\.[a-f0-9]+|style|slideshow)\.css[^\"]*"\s*/?>', '', html)
    html = re.sub(r'<script src="/(?:site\.[a-f0-9]+|app|slideshow)\.js[^\"]*" defer></script>', '', html)
    html = re.sub(r'<link rel="preload" href="/assets/fonts/[^\"]+"[^>]*>', '', html)
    html = html.replace('</head>', '<link rel="preload" href="/assets/fonts/barlow-condensed-600.woff2" as="font" type="font/woff2" crossorigin><link rel="stylesheet" href="/' + names['css'] + '"></head>')
    if name == 'index.html':
        html = html.replace('</body>', '<script src="/' + names['js'] + '" defer></script></body>')
    path.write_text(html, encoding='utf-8')
print(json.dumps({**names, 'cssBytes': len(css.encode()), 'cssGzipBytes': len(gzip.compress(css.encode())), 'jsBytes': len(js.encode()), 'jsGzipBytes': len(gzip.compress(js.encode()))}, indent=2))
