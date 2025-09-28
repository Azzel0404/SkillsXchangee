// Firebase Configuration
// Replace the placeholder values with your actual Firebase project credentials
const firebaseConfig = {
    apiKey: "your-api-key-here",
    authDomain: "skillsxchange-26855.firebaseapp.com",
    databaseURL: "https://skillsxchange-26855-default-rtdb.asia-southeast1.firebaseapp.com/",
    projectId: "skillsxchange-26855",
    storageBucket: "skillsxchange-26855.appspot.com",
    messagingSenderId: "your-messaging-sender-id",
    appId: "your-app-id-here"
};

// Initialize Firebase
try {
    if (typeof firebase !== 'undefined') {
        firebase.initializeApp(firebaseConfig);
        console.log('✅ Firebase initialized successfully from firebase-config.js');
    } else {
        console.error('❌ Firebase not loaded. Make sure Firebase CDN is included.');
    }
} catch (error) {
    console.error('❌ Error initializing Firebase:', error);
}