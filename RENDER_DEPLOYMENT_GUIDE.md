# Render.com Deployment Guide for DiozStore

## Step 1: Push Updated Code to GitHub

Your `db-config.php` is now updated to use environment variables. Push the changes:

```bash
git add db-config.php
git commit -m "Update db-config to use environment variables"
git push origin main
```

---

## Step 2: Set Up MySQL Database (Required)

Render.com does not include MySQL, so you need an external database provider. Choose one:

### Option A: **PlanetScale** (Recommended - Free tier available)
1. Go to https://planetscale.com/
2. Sign up (free account)
3. Create a new database
4. Get connection details:
   - **Host**: `aws.connect.psdb.cloud`
   - **Username**: `<your_username>`
   - **Password**: `<your_password>`
   - **Database**: `<your_database_name>`
   - **Port**: `3306`

### Option B: **Railway** (Free $5/month credit)
1. Go to https://railway.app/
2. Sign up
3. Create MySQL plugin
4. Get connection details from Railway dashboard

### Option C: **AWS RDS** (Paid, reliable)
1. Go to https://aws.amazon.com/rds/
2. Create MySQL instance
3. Get endpoint and credentials

---

## Step 3: Create Render.com Web Service

1. Go to https://dashboard.render.com/web/new
2. Fill in the form:
   - **Name**: `diozstore` (or your preferred name)
   - **Language**: `Docker`
   - **Repository**: Connect your GitHub repo (https://github.com/DiozzStore/diozs.store)
   - **Branch**: `main`
   - **Root Directory**: `.` (leave as is)

3. **Instance Type**: Select `Free` tier (if testing) or `Starter` (for production)

---

## Step 4: Add Environment Variables

On the Render.com deployment page, scroll to **"Environment Variables"** section and add:

```
DB_HOST=<your_planetscale_host>
DB_NAME=<your_database_name>
DB_USER=<your_username>
DB_PASS=<your_password>
DB_PORT=3306
```

**Example from PlanetScale:**
```
DB_HOST=aws.connect.psdb.cloud
DB_NAME=diozstore
DB_USER=admin_user
DB_PASS=pscale_pw_abc123xyz...
DB_PORT=3306
```

---

## Step 5: Deploy

1. Click **"Create Web Service"** button
2. Render will:
   - Pull your code from GitHub
   - Build the Docker image
   - Deploy the PHP/Apache container
   - Assign a public URL (e.g., `https://diozstore.onrender.com`)

---

## Step 6: Monitor Deployment

1. Check logs in Render dashboard
2. Wait for "Build completed successfully" and "Service deployed"
3. Visit your URL to test

---

## Troubleshooting

**Database Connection Error?**
- Verify environment variables are exactly correct
- Check if MySQL database credentials are valid
- Ensure firewall allows connections from Render's IP

**Container Won't Start?**
- Check build logs for errors
- Verify Dockerfile is correct
- Look for PHP errors in logs

**Port Issues?**
- Your app runs on port 80 (set in Dockerfile)
- Render automatically forwards traffic to this port

---

## Security Notes

✅ **DO:** Use environment variables (you just did!)
✅ **DO:** Never commit `.env` files with passwords
✅ **DO:** Rotate database passwords regularly

❌ **DON'T:** Hardcode credentials in code
❌ **DON'T:** Commit passwords to GitHub
❌ **DON'T:** Share your `.env` file

---

## Quick Reference: Environment Variables to Set

| Variable | Example | Where to Get |
|----------|---------|--------------|
| `DB_HOST` | `aws.connect.psdb.cloud` | From your database provider |
| `DB_NAME` | `diozstore` | From your database provider |
| `DB_USER` | `admin_user` | From your database provider |
| `DB_PASS` | `pscale_pw_xxx` | From your database provider |
| `DB_PORT` | `3306` | Usually 3306 for MySQL |

---

**Next Steps:**
1. Create a database account (PlanetScale recommended)
2. Get credentials
3. Deploy on Render.com with environment variables set
4. Test your application at the Render URL
