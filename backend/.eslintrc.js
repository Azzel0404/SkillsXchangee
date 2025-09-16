module.exports = {
    env: {
        browser: true,
        es2021: true,
        jquery: true
    },
    extends: [
        'eslint:recommended'
    ],
    parserOptions: {
        ecmaVersion: 12,
        sourceType: 'module'
    },
    rules: {
        // Disable rules that conflict with Blade templates
        'no-undef': 'off',
        'no-unused-vars': 'warn',
        'no-console': 'off',
        'no-alert': 'off',
        'no-global-assign': 'off',
        'no-implicit-globals': 'off'
    },
    globals: {
        // Laravel/Pusher globals
        'Echo': 'readonly',
        'Pusher': 'readonly',
        'Laravel': 'readonly',
        'axios': 'readonly',
        'moment': 'readonly',
        'jQuery': 'readonly',
        '$': 'readonly',
        // WebRTC globals
        'RTCPeerConnection': 'readonly',
        'RTCSessionDescription': 'readonly',
        'RTCIceCandidate': 'readonly',
        'navigator': 'readonly',
        'window': 'readonly',
        'document': 'readonly',
        'console': 'readonly',
        'setTimeout': 'readonly',
        'clearTimeout': 'readonly',
        'setInterval': 'readonly',
        'clearInterval': 'readonly',
        'fetch': 'readonly',
        'Promise': 'readonly',
        'Date': 'readonly',
        'Math': 'readonly',
        'JSON': 'readonly',
        'parseInt': 'readonly',
        'parseFloat': 'readonly',
        'isNaN': 'readonly',
        'isFinite': 'readonly',
        'encodeURIComponent': 'readonly',
        'decodeURIComponent': 'readonly',
        'encodeURI': 'readonly',
        'decodeURI': 'readonly',
        'escape': 'readonly',
        'unescape': 'readonly',
        'eval': 'readonly',
        'Function': 'readonly',
        'Object': 'readonly',
        'Array': 'readonly',
        'String': 'readonly',
        'Number': 'readonly',
        'Boolean': 'readonly',
        'RegExp': 'readonly',
        'Error': 'readonly',
        'TypeError': 'readonly',
        'ReferenceError': 'readonly',
        'SyntaxError': 'readonly',
        'RangeError': 'readonly',
        'EvalError': 'readonly',
        'URIError': 'readonly'
    },
    overrides: [
        {
            files: ['*.blade.php'],
            rules: {
                // Disable all rules for Blade files
                'no-undef': 'off',
                'no-unused-vars': 'off',
                'no-console': 'off',
                'no-alert': 'off',
                'no-global-assign': 'off',
                'no-implicit-globals': 'off',
                'no-redeclare': 'off',
                'no-var': 'off',
                'prefer-const': 'off',
                'prefer-let': 'off',
                'no-constant-condition': 'off',
                'no-dupe-keys': 'off',
                'no-duplicate-case': 'off',
                'no-empty': 'off',
                'no-ex-assign': 'off',
                'no-extra-boolean-cast': 'off',
                'no-extra-semi': 'off',
                'no-func-assign': 'off',
                'no-inner-declarations': 'off',
                'no-invalid-regexp': 'off',
                'no-irregular-whitespace': 'off',
                'no-obj-calls': 'off',
                'no-sparse-arrays': 'off',
                'no-unreachable': 'off',
                'use-isnan': 'off',
                'valid-typeof': 'off'
            }
        }
    ]
};
