// Firebase Configuration v12 - Fallback for non-module environments
// This file provides a fallback when Firebase modules are not available

// Wait for Firebase to be available from the module script
function waitForFirebase() {
    return new Promise((resolve) => {
        const checkFirebase = () => {
            if (window.firebaseDatabase) {
                console.log('✅ Firebase v12 available from module script');
                resolve(true);
            } else if (typeof firebase !== 'undefined' && firebase.database) {
                console.log('✅ Firebase v8 available from CDN');
                resolve(true);
            } else {
                setTimeout(checkFirebase, 100);
            }
        };
        checkFirebase();
    });
}

// Initialize when Firebase is available
waitForFirebase().then(() => {
    console.log('✅ Firebase configuration ready');
});