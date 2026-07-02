import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.arcane.skillswap',
  appName: 'SkillSwap',
  webDir: 'dist',
  server: {
    url: 'https://frontend-production-27b9e.up.railway.app',
    cleartext: false
  }
};

export default config;