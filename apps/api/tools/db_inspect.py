import sys
from pathlib import Path
sys.path.append(str(Path(__file__).resolve().parent.parent))

from app.database import engine
from sqlalchemy import text
import pymysql
import os

def main():
    print("--- SHOW TABLES ---")
    with engine.connect() as conn:
        tables = conn.execute(text('SHOW TABLES')).fetchall()
        for t in tables:
            print(t[0])
            print("  ->", conn.execute(text(f'SHOW CREATE TABLE {t[0]}')).fetchone()[1])
            print()

    print("--- SHOW INNODB STATUS ---")
    try:
        # Assumes local test environment as per original show_innodb.py
        conn = pymysql.connect(host='localhost', user='root', password='', database='yii2advanced')
        cursor = conn.cursor()
        cursor.execute('SHOW ENGINE INNODB STATUS')
        status = cursor.fetchone()[2]
        start = status.find('LATEST FOREIGN KEY ERROR')
        if start != -1:
            end = status.find('TRANSACTIONS', start)
            print(status[start:end])
        else:
            print("No recent foreign key errors found.")
        conn.close()
    except Exception as e:
        print(f"Could not connect to MySQL to show InnoDB status: {e}")

if __name__ == "__main__":
    main()
