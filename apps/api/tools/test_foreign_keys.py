import pymysql
conn = pymysql.connect(host='localhost', user='root', password='', database='yii2advanced')
cursor = conn.cursor()
try:
    cursor.execute('''CREATE TABLE sensors (
        id VARCHAR(50) NOT NULL, 
        name VARCHAR(100) NOT NULL, 
        data_type VARCHAR(100) NOT NULL, 
        min_threshold FLOAT, 
        max_threshold FLOAT, 
        is_online BOOL, 
        area_id INTEGER, 
        created_at DATETIME, 
        updated_at DATETIME, 
        PRIMARY KEY (id), 
        FOREIGN KEY(area_id) REFERENCES areas (id) ON DELETE CASCADE
    ) ENGINE=InnoDB''')
except Exception as e:
    cursor.execute('SHOW WARNINGS')
    print(cursor.fetchall())
