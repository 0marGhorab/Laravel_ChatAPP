# Testing with ngrok (multiple devices)

Use ngrok to expose your local Laravel app so you can open it on phones, tablets, or other computers on the same network.

---

## Full restart checklist (do this when design doesn’t load on other devices)

1. **Stop everything**  
   Stop ngrok (Ctrl+C), `npm run dev` (Ctrl+C), and `php artisan serve` (Ctrl+C).

2. **Set your ngrok URL in `.env`**  
   Set both (replace with your current ngrok URL):
   ```env
   APP_URL=https://YOUR-SUBDOMAIN.ngrok-free.dev
   ASSET_URL=https://YOUR-SUBDOMAIN.ngrok-free.dev
   ```

3. **Clear Laravel caches**
   ```bash
   cd "E:\Ecolor Technologies\ChatAPP"
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

4. **Build front-end assets**
   ```bash
   npm run build
   ```
   Confirm `public/build/manifest.json` and `public/build/assets/*.css` and `*.js` exist.

5. **Start only Laravel** (no `npm run dev` when testing via ngrok)
   ```bash
   php artisan serve --host=0.0.0.0
   ```

6. **Start ngrok**
   ```bash
   ngrok http 8000
   ```
   If the Forwarding URL is **different** from what you put in `.env`, update `APP_URL` and `ASSET_URL` in `.env` to that URL, then run again:
   ```bash
   php artisan config:clear
   ```
   Restart `php artisan serve --host=0.0.0.0`.

7. **Open the ngrok HTTPS URL on the other device**  
   Use the URL from the ngrok window (e.g. `https://xxxx.ngrok-free.dev`). Do a hard refresh if the design still doesn’t load.

**If design still doesn’t load on the other device**

- In `.env` use **relative** asset URLs so the browser always uses the current host (the ngrok URL):
  ```env
  ASSET_URL=/
  ```
- Clear config again and restart Laravel:
  ```bash
  php artisan config:clear
  php artisan serve --host=0.0.0.0
  ```
- On the other device, confirm you’re opening the **HTTPS** ngrok URL from the ngrok window (not `http://127.0.0.1:8000` or a different URL).
- If it still fails, on the other device try: open the ngrok URL, then long-press refresh / “Request desktop site” or clear the browser cache for that site, then reload.

---

## 1. Install ngrok

- **Windows (scoop):** `scoop install ngrok`
- **Windows (Chocolatey):** `choco install ngrok`
- **Or download:** [ngrok.com/download](https://ngrok.com/download)  
- **One-off (no install):** `npx ngrok http 8000`

Sign up at [ngrok.com](https://ngrok.com) and run `ngrok config add-authtoken YOUR_TOKEN` once.

## 2. Run Laravel on all interfaces

So the server accepts connections from the tunnel and your network:

```bash
php artisan serve --host=0.0.0.0
```

Leave this terminal open.

## 3. Start the ngrok tunnel

In a **second terminal**:

```bash
ngrok http 8000
```

Copy the **HTTPS** URL ngrok shows (e.g. `https://abc123.ngrok-free.app`).

## 4. Use the ngrok URL on other devices

Open that URL on your phone, tablet, or another PC. Everyone on the tunnel will hit the same local app.

## 5. Fix redirects and links (optional)

So login redirects and links use the ngrok URL instead of localhost:

1. In `.env` set:
   ```env
   APP_URL=https://YOUR-SUBDOMAIN.ngrok-free.app
   ```
   (Replace with the URL from step 3.)

2. Restart the Laravel server (step 2).

With free ngrok the URL changes each time you run `ngrok http 8000`, so you’ll need to update `APP_URL` when you restart ngrok. Paid plans can use a fixed domain.

## 6. Make CSS/JS work on other devices (required for design)

The app loads CSS and JS via Vite. In dev, assets are served from `localhost:5173`, which other devices cannot reach, so the design won’t load when you open the ngrok URL.

**Fix:** build assets so Laravel serves them from the same domain as the page (your ngrok URL):

```bash
npm run build
```

Then open the ngrok URL on your phone/tablet again — styles and scripts will load. You do **not** need to run `npm run dev` when testing via ngrok; the built files in `public/build` are enough.
