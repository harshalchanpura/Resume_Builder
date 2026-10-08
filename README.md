# ResumeCraft - Railway deployment

## 1. Upload to GitHub (IMPORTANT)
Unzip first, then push the CONTENTS so `Dockerfile` and `index.php`
are at the TOP LEVEL of the repo (not inside a sub-folder).

    unzip ResumeCraft_Railway.zip -d resumecraft && cd resumecraft
    git init && git add . && git commit -m "ResumeCraft"
    git branch -M main
    git remote add origin https://github.com/<you>/<repo>.git
    git push -u origin main --force

## 2. Railway project
1. New Project -> Deploy from GitHub repo -> pick the repo.
   Leave **Root Directory empty**. Railway builds the Dockerfile.
2. In the same project: **+ New -> Database -> MySQL**.

## 3. Connect the web service to MySQL
Web service -> **Variables** -> **New Variable** (use "Add Reference" or paste).
Replace `MySQL` with your MySQL service's exact name:

    MYSQLHOST      = ${{MySQL.MYSQLHOST}}
    MYSQLPORT      = ${{MySQL.MYSQLPORT}}
    MYSQLUSER      = ${{MySQL.MYSQLUSER}}
    MYSQLPASSWORD  = ${{MySQL.MYSQLPASSWORD}}
    MYSQLDATABASE  = ${{MySQL.MYSQLDATABASE}}

(Alternative: one variable only -> `MYSQL_URL = ${{MySQL.MYSQL_URL}}`)

Also set an admin login for /admin:

    ADMIN_USER     = admin
    ADMIN_PASSWORD = <choose-a-strong-password>

Tables are created automatically on first visit. `database.sql` is optional.

## 4. Public URL
Web service -> Settings -> Networking -> **Generate Domain**.

## 5. Keep uploaded photos (optional)
Web service -> **+ Volume** -> mount path `/var/www/html/uploads`.

## Troubleshooting
- "Railpack failed to prepare the build": Dockerfile is not at repo root.
- "Database connection failed": variables in step 3 missing/typo in service name.
- Check logs: Web service -> Deployments -> View logs.
