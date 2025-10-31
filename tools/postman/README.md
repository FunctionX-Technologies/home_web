Import these into Postman:

1) Collection: FunctionX.postman_collection.json
2) Environment: FunctionX.postman_environment.json

Steps:
- In Postman, import the collection file and environment file.
- Select the `functionx-local` environment.
- Edit the `base_url` variable to match how you access the app in browser if different (e.g., http://localhost/functionx/functionx_backend/public/index.php or include port :8080).
- Run `Health - DB` first. If it returns {
    "db_ok": true,
    "users_count": 0
  }
  you're ready to run `Auth - Register` and `Auth - Login`.

Notes:
- Ensure `app/Config/Database.php` matches your MySQL credentials. Current defaults are username=root,password=root,database=functionx_db.
- Ensure `.env` sets CI_ENVIRONMENT = development so errors are visible.
- If you get 500 or no response, check writable/logs/ and your Apache/PHP logs.
