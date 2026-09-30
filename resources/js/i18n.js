import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

const customBackend = {
    type: 'backend',
    init: function (services, backendOptions) {
        this.services = services;
        this.options = backendOptions;
    },
    read: function (language, namespace, callback) {
        const loadPath = window.route ? window.route('languages.translations', language) : `/translations/${language}`;

        fetch(loadPath)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                const authLayoutDirection = window.auth?.user?.layout_direction || window.auth?.layout_direction;
                const rtlLanguages = ['ar', 'he'];
                const detectedDirection = rtlLanguages.includes(language) ? 'rtl' : 'ltr';

                // Use auth layout direction if available, otherwise detect from language
                const direction = authLayoutDirection || data.layoutDirection || detectedDirection;

                document.documentElement.dir = direction;
                document.documentElement.style.direction = direction;
                document.body.dir = direction;
                document.body.style.direction = direction;

                callback(null, data.translations);
            })
            .catch(error => callback(error, null));
    }
};

const userLang = window.auth?.user?.lang || window.auth?.lang || 'en';

// Apply initial RTL direction from auth or detect from language
const initialLayoutDirection = window.auth?.user?.layout_direction || window.auth?.layout_direction;
if (initialLayoutDirection) {
    document.documentElement.dir = initialLayoutDirection;
    document.documentElement.style.direction = initialLayoutDirection;
    document.body.dir = initialLayoutDirection;
    document.body.style.direction = initialLayoutDirection;
} else {
    // For guest users, detect RTL from language
    const rtlLanguages = ['ar', 'he'];
    const isRTL = rtlLanguages.includes(userLang);
    const direction = isRTL ? 'rtl' : 'ltr';
    document.documentElement.dir = direction;
    document.documentElement.style.direction = direction;
    document.body.dir = direction;
    document.body.style.direction = direction;
}

// Function to apply direction based on language
const applyDirection = (language) => {
    const authLayoutDirection = window.auth?.user?.layout_direction || window.auth?.layout_direction;
    if (authLayoutDirection) {
        document.documentElement.dir = authLayoutDirection;
        document.documentElement.style.direction = authLayoutDirection;
        document.body.dir = authLayoutDirection;
        document.body.style.direction = authLayoutDirection;
    } else {
        const rtlLanguages = ['ar', 'he'];
        const direction = rtlLanguages.includes(language) ? 'rtl' : 'ltr';
        document.documentElement.dir = direction;
        document.documentElement.style.direction = direction;
        document.body.dir = direction;
        document.body.style.direction = direction;
    }
};

i18n
    .use(customBackend)
    .use(initReactI18next)
    .init({
        lng: userLang,
        fallbackLng: userLang,
        interpolation: {
            escapeValue: false,
        },
        react: {
            useSuspense: false
        },
        cache: {
            enabled: false
        }
    });

// Listen to language change events and apply direction
i18n.on('languageChanged', (lng) => {
    applyDirection(lng);
});

export default i18n;