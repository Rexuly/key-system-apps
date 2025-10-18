// create_admin.js
// Usage: node create_admin.js email@example.com YourStrongPassword
// Requires: npm i firebase-admin

const admin = require('firebase-admin');
const fs = require('fs');
const path = require('path');

if (process.argv.length < 4) {
  console.error('Usage: node create_admin.js email@example.com password');
  process.exit(1);
}

const email = process.argv[2];
const password = process.argv[3];

// Path to your service account JSON (download from Firebase Console -> Project Settings -> Service accounts).
// IMPORTANT: do NOT commit this file to GitHub.
const serviceAccountPath = path.join(__dirname, 'serviceAccountKey.json');
if (!fs.existsSync(serviceAccountPath)) {
  console.error('Missing serviceAccountKey.json. Download from Firebase Console and place here.');
  process.exit(1);
}

// Initialize
admin.initializeApp({
  credential: admin.credential.cert(require(serviceAccountPath))
});

(async () => {
  try {
    // Create user
    const userRecord = await admin.auth().createUser({
      email,
      password,
      emailVerified: true,
      disabled: false
    });

    // Set admin custom claim
    await admin.auth().setCustomUserClaims(userRecord.uid, { admin: true });
    console.log('✅ Created admin user:', userRecord.uid, email);

    // Optionally: print a one-time login link (requires Dynamic Links or a custom flow). We'll just note that
    console.log('Admin created. Admin can now sign in with email/password.');
    process.exit(0);
  } catch (err) {
    console.error('Error:', err.message || err);
    process.exit(1);
  }
})();
