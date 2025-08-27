import os
from bs4 import BeautifulSoup
import json
import re

FRONTEND_DIRS = ["../pages", "../content"]
EXCLUDE_FILES = ["build_knowledge.py"]
FORBIDDEN_KEYWORDS = [
    "<?", "?>", "$", "SELECT", "INSERT", "UPDATE", "DELETE", "WHERE", "GROUP BY",
    "ORDER BY", "LIMIT", "JOIN", "--", "//", "function", "console.log", "post("
]

knowledge_data = []

def is_useful_paragraph(p):
    p = p.strip()
    if len(p.split()) < 3:
        return False
    if not any(c.isalpha() for c in p):
        return False
    if any(k.lower() in p.lower() for k in FORBIDDEN_KEYWORDS):
        return False
    return True

for web_dir in FRONTEND_DIRS:
    for root, dirs, files in os.walk(web_dir):
        for file in files:
            if file in EXCLUDE_FILES:
                continue
            if file.endswith(".php") or file.endswith(".html"):
                path = os.path.join(root, file)
                try:
                    with open(path, "r", encoding="utf-8") as f:
                        html = f.read()
                except UnicodeDecodeError:
                    try:
                        with open(path, "r", encoding="windows-1252") as f:
                            html = f.read()
                    except:
                        print("Bỏ qua file:", path)
                        continue

                soup = BeautifulSoup(html, "html.parser")
                for tag in soup(["script", "style", "head", "meta"]):
                    tag.decompose()

                text = soup.get_text(separator="\n")
                paragraphs = [p.strip() for p in text.split("\n") if p.strip() and is_useful_paragraph(p)]

                # Gộp tất cả đoạn trong cùng một file thành một entry
                if paragraphs:
                    full_text = " ".join(paragraphs)
                    knowledge_data.append({
                        "text": full_text,
                        "source": path.replace("\\", "/")
                    })

with open("knowledge_base.json", "w", encoding="utf-8") as f:
    json.dump(knowledge_data, f, ensure_ascii=False, indent=2)

print("Xong! Có", len(knowledge_data), "trang được lưu vào knowledge_base.json")
