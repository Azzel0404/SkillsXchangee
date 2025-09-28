/**
 * Firebase Configuration for SkillsXchangee
 * This file contains the Firebase configuration for video calling
 */

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyDlx5VjhobiTlqtv69SciHifC7p_xgHELs",
    authDomain: "skillsxchange-c2604.firebaseapp.com",
    databaseURL: "https://skillsxchange-c2604-default-rtdb.asia-southeast1.firebaseio.com",
    projectId: "skillsxchange-c2604",
    storageBucket: "skillsxchange-c2604.firebasestorage.app",
    messagingSenderId: "478530945561",
    appId: "1:478530945561:web:646441ceeb5d5c71c02088"
};

// Make config available globally
window.firebaseConfig = firebaseConfig;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = firebaseConfig;
}