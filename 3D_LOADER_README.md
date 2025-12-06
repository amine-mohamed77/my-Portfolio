# 🚀 Professional 3D Loading Screen

## 📦 What You Got

A **production-ready, professional 3D loading screen** with:

✅ **3D Torus Knot** - Low-poly geometric shape
✅ **PBR Materials** - Metallic + Translucent glass effect
✅ **Smooth Animations** - Rotation, bobbing, tilting, pulsing
✅ **Progress Bar** - Animated with percentage display
✅ **Fully Customizable** - CSS variables + JSON config
✅ **Performance Optimized** - 60fps, GPU detection, reduced-motion support
✅ **Responsive** - Works on all devices
✅ **Fallback Support** - CSS loader if WebGL not available

---

## 🎯 Quick Start

### 1. **View the Demo**

Open in your browser:
```
http://localhost/Portfolio/3d-loader.html
```

### 2. **Customize Colors**

Edit the CSS variables in `3d-loader.html`:

```css
:root {
    --loader-color: #3b82f6;        /* Change to your brand color */
    --loader-accent: #8b5cf6;       /* Accent color */
    --loader-rotation-speed: 1;     /* 0.5 = slow, 2 = fast */
}
```

### 3. **Test Different Themes**

Try these color combinations:

**🔵 Blue (Default)**
```css
--loader-color: #3b82f6;
--loader-accent: #8b5cf6;
```

**🟢 Green**
```css
--loader-color: #10b981;
--loader-accent: #34d399;
--bg-gradient-from: #064e3b;
--bg-gradient-to: #065f46;
```

**🔴 Red**
```css
--loader-color: #ef4444;
--loader-accent: #f97316;
--bg-gradient-from: #7f1d1d;
--bg-gradient-to: #991b1b;
```

**🟣 Purple**
```css
--loader-color: #a855f7;
--loader-accent: #ec4899;
--bg-gradient-from: #581c87;
--bg-gradient-to: #6b21a8;
```

**🌊 Cyberpunk**
```css
--loader-color: #06b6d4;
--loader-accent: #ec4899;
--bg-gradient-from: #0c0a09;
--bg-gradient-to: #1c1917;
--loader-rotation-speed: 1.5;
```

---

## 📁 Files Included

| File | Description |
|------|-------------|
| `3d-loader.html` | Complete standalone demo |
| `loader-script.js` | Reusable JavaScript module |
| `loader-config.json` | Configuration file with themes |
| `3D_LOADER_INTEGRATION.md` | Full integration guide |
| `3D_LOADER_README.md` | This file |

---

## 🔌 Integration

### Option 1: Add to Your Portfolio

Copy the loading screen HTML from `3d-loader.html` and paste it at the **top** of your `index.php` (right after `<body>`).

### Option 2: Use as Separate Component

Include the script:
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r150/three.min.js"></script>
<script src="loader-script.js"></script>
```

### Option 3: Load from JSON Config

```javascript
fetch('loader-config.json')
    .then(res => res.json())
    .then(config => {
        // Apply theme
        const theme = config.themes.cyberpunk; // or blue, green, red, etc.
        document.documentElement.style.setProperty('--loader-color', theme.colorHex);
        document.documentElement.style.setProperty('--loader-accent', theme.accentColor);
    });
```

---

## ⚙️ API

### Hide Loader Manually

```javascript
// Hide immediately
window.Loader3D.hide();

// Hide after 3 seconds
setTimeout(() => window.Loader3D.hide(), 3000);
```

### Update Progress Manually

```javascript
// Set progress to 50%
window.Loader3D.updateProgress(50);

// Simulate loading
let progress = 0;
const interval = setInterval(() => {
    progress += 10;
    window.Loader3D.updateProgress(progress);
    if (progress >= 100) clearInterval(interval);
}, 200);
```

---

## 🎨 Customization

### Change Rotation Speed

```css
--loader-rotation-speed: 2; /* Faster */
--loader-rotation-speed: 0.5; /* Slower */
```

### Change Material Properties

```css
--loader-metalness: 1; /* More metallic */
--loader-roughness: 0; /* More glossy */
```

### Change Geometry

Edit `loader-script.js`:

```javascript
// Replace torus knot with icosahedron
const geometry = new THREE.IcosahedronGeometry(1.5, 1);

// Or octahedron
const geometry = new THREE.OctahedronGeometry(1.5, 2);
```

---

## 📱 Features

### ✅ Responsive Design
- Desktop: 400x400px canvas
- Mobile: 300x300px canvas
- Auto-adjusts text size

### ✅ Performance Optimized
- Auto-detects GPU capability
- Reduces geometry complexity on weak devices
- Targets 60fps
- Supports reduced-motion preferences

### ✅ Accessibility
- Reduced-motion support
- Keyboard accessible
- Screen reader friendly

### ✅ Browser Support
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers

---

## 🐛 Troubleshooting

### Loader Not Showing?

1. Check if Three.js loaded:
```javascript
console.log(typeof THREE); // Should be "object"
```

2. Check browser console for errors (F12)

3. Verify canvas element exists:
```javascript
console.log(document.getElementById('loader-canvas'));
```

### Performance Issues?

1. Reduce pixel ratio:
```javascript
renderer.setPixelRatio(1);
```

2. Disable antialiasing:
```javascript
renderer = new THREE.WebGLRenderer({ antialias: false });
```

3. Lower geometry detail in `loader-script.js`

### WebGL Not Supported?

The loader automatically falls back to a CSS spinner if WebGL is not available.

---

## 📚 Documentation

For complete documentation, see:
- **Integration Guide**: `3D_LOADER_INTEGRATION.md`
- **Configuration**: `loader-config.json`
- **Three.js Docs**: https://threejs.org/docs/

---

## 🎯 Next Steps

1. ✅ **Test the demo**: `http://localhost/Portfolio/3d-loader.html`
2. ✅ **Customize colors**: Edit CSS variables
3. ✅ **Try different themes**: Use presets from `loader-config.json`
4. ✅ **Integrate into your portfolio**: Follow `3D_LOADER_INTEGRATION.md`
5. ✅ **Test on mobile**: Check responsive behavior
6. ✅ **Deploy**: Add to production site

---

## 💡 Pro Tips

1. **Match your brand colors**: Use your portfolio's color scheme
2. **Keep it fast**: Don't set `minLoadTime` too high
3. **Test on slow connections**: Simulate 3G in DevTools
4. **Add real loading logic**: Replace simulated progress with actual asset loading
5. **Optimize for mobile**: Test on real devices

---

## ✨ Features Showcase

### 🎨 Visual Effects
- Metallic + Glass hybrid material
- Environment reflections
- Soft glow behind loader
- Smooth gradient background
- Pulsing scale animation

### 🎬 Animations
- Smooth rotation (easeInOut)
- Subtle bobbing movement
- Gentle tilting
- Pause on hover
- Shimmer effect on progress bar

### ⚡ Performance
- 60fps target
- GPU-accelerated rendering
- Automatic quality adjustment
- Reduced-motion support
- Efficient cleanup on hide

---

## 🎉 You're All Set!

Your **professional 3D loading screen** is ready to use!

**Demo**: `http://localhost/Portfolio/3d-loader.html`

**Enjoy!** 🚀✨
