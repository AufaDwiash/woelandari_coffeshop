import json
from pathlib import Path

from PIL import Image


root = Path(__file__).resolve().parents[1]
frame_dir = root / "output" / "demo-gif-frames"
docs_dir = root / "docs"
output = docs_dir / "woelandari-demo.gif"

frames_data = json.loads((frame_dir / "frames.json").read_text())
images = []
durations = []

for item in frames_data:
    with Image.open(frame_dir / item["file"]) as image:
        width = 720
        height = round(image.height * width / image.width)
        resized = image.resize((width, height), Image.Resampling.LANCZOS)
        images.append(resized.convert("P", palette=Image.Palette.ADAPTIVE, colors=96))
        durations.append(item["duration"])

docs_dir.mkdir(exist_ok=True)
images[0].save(
    output,
    save_all=True,
    append_images=images[1:],
    duration=durations,
    loop=0,
    optimize=True,
)

print(output)
