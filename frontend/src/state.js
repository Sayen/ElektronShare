import { reactive } from 'vue';

const savedDark = localStorage.getItem('dark_mode') === 'true';
if (savedDark) {
    document.documentElement.classList.add('dark');
}

export const globalState = reactive({
    darkMode: savedDark,
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('dark_mode', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('dark_mode', 'false');
        }
    }
});
