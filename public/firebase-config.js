/**
 * Firebase Configuration for SkillsXchangee Video Calls
 * Replace these values with your actual Firebase project configuration
 */

const firebaseConfig = {
    // Firebase project configuration for Skillsxchange
    apiKey: "AIzaSyDKk5L6noLC1DcQcE2ihT199eoIrZkzclY",
    authDomain: "skillsxchange-42c62.firebaseapp.com",
    databaseURL: "https://skillsxchange-42c62-default-rtdb.firebaseio.com",
    projectId: "skillsxchange-42c62",
    storageBucket: "skillsxchange-42c62.firebasestorage.app",
    messagingSenderId: "1096126152239",
    appId: "1:1096126152239:web:a9ecf3f3df9e20dc4310da",
    measurementId: "G-XYE1EJMOYG"
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = firebaseConfig;
} else {
    window.firebaseConfig = firebaseConfig;
}
