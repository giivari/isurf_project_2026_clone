import os
import glob

search_text = "http://localhost:8000/api"
replace_text = "https://api.digdaya.net/isurf/v1"

files_to_check = [
    "apps/web/frontend/web/js/isurf-api.js",
    "apps/web/frontend/views/site/area-details.php",
    "apps/web/frontend/views/site/monitoring.php",
    "apps/web/frontend/views/site/request-data.php",
    "apps/web/frontend/views/site/clean_script.php"
]

for filepath in files_to_check:
    full_path = os.path.join("c:/Users/givar/KULIAH/capstone/ilkom-isurf-project", filepath)
    if os.path.exists(full_path):
        with open(full_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content = content.replace(search_text, replace_text)
        
        with open(full_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated {filepath}")
    else:
        print(f"Not found: {filepath}")
