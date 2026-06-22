import pymysql

conn = pymysql.connect(host='localhost', user='root', password='', database='yii2advanced')
cur = conn.cursor()

# Get current hashes
cur.execute("SELECT id, username, password_hash FROM users")
rows = cur.fetchall()

for r in rows:
    old_hash = r[2]
    # Replace $2b$ with $2y$ (cryptographically identical, but Yii2 only accepts $2y$)
    new_hash = old_hash.replace('$2b$', '$2y$', 1)
    cur.execute("UPDATE users SET password_hash = %s WHERE id = %s", (new_hash, r[0]))
    print(f"  {r[1]}: {old_hash[:10]}... -> {new_hash[:10]}...")

conn.commit()
print(f"\nUpdated {len(rows)} users. Prefix changed from $2b$ to $2y$.")
print("You can now login with username 'admin' and password 'password123'")
conn.close()
