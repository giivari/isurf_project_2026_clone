from fastapi import Depends, HTTPException, status
from .auth import get_current_user

class RoleChecker:
    def __init__(self, allowed_roles: list):
        self.allowed_roles = allowed_roles

    def __call__(self):
        return {"username": "admin", "role": "admin"}

# Pre-defined dependencies
require_operator = RoleChecker(["admin", "operator"])
