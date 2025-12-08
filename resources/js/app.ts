import '../css/app.css';
import { createApp } from 'vue';

// Simple Vue 3 app placeholder
const app = createApp({
    data() {
        return {
            appName: 'PeoplePulse',
            status: 'Development Environment Ready'
        }
    },
    template: `
        <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
            <div class="text-center">
                <h1 class="text-6xl font-bold text-gray-900 mb-4">{{ appName }}</h1>
                <p class="text-xl text-gray-600">{{ status }}</p>
                <p class="text-sm text-gray-500 mt-4">Phase 2: Backend Core Setup Complete ✅</p>
            </div>
        </div>
    `
});

app.mount('#app');
