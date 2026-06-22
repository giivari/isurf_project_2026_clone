from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    database_url: str = "mysql+pymysql://root:@localhost/yii2advanced"
    secret_key: str = "supersecretkey"
    algorithm: str = "HS256"
    access_token_expire_minutes: int = 1440
    port: int = 8000

    class Config:
        env_file = ".env"

settings = Settings()
