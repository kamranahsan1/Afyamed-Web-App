# Firebase Admin credentials

Put your service account JSON here:

```text
Backend/storage/app/firebase/service-account.json
```

Full local path on this machine:

```text
C:\Users\kamra\OneDrive\Desktop\Afyamed project\Backend\storage\app\firebase\
```

Download from:
Firebase Console → Project settings → Service accounts → Generate new private key

Then in `.env`:

```env
FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json
FIREBASE_PROJECT_ID=afyamed-7b8a1
FIREBASE_AUTH_BYPASS=false
```

Never commit `service-account.json`.
