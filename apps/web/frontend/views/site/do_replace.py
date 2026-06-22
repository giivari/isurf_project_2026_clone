with open(r'c:\Users\givar\KULIAH\capstone\ilkom-isurf-project\apps\web\frontend\views\site\area-details.php', 'r', encoding='utf-8') as f:
    orig = f.read()
with open(r'c:\Users\givar\KULIAH\capstone\ilkom-isurf-project\apps\web\frontend\views\site\clean_script.php', 'r', encoding='utf-8') as f:
    clean = f.read()

orig = orig.replace('class="text-2xl font-bold"', 'class="text-xl font-bold"')
idx = orig.find('<script>')
if idx != -1:
    orig = orig[:idx] + clean

with open(r'c:\Users\givar\KULIAH\capstone\ilkom-isurf-project\apps\web\frontend\views\site\area-details.php', 'w', encoding='utf-8') as f:
    f.write(orig)
