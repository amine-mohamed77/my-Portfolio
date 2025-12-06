/**
 * Professional 3D Loader Script
 * Three.js-based loading screen with PBR materials
 * @version 1.0.0
 */

(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        colorHex: getComputedStyle(document.documentElement).getPropertyValue('--loader-color')?.trim() || '#3b82f6',
        accentColor: getComputedStyle(document.documentElement).getPropertyValue('--loader-accent')?.trim() || '#8b5cf6',
        rotationSpeed: parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--loader-rotation-speed')) || 1,
        metalness: parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--loader-metalness')) || 0.8,
        roughness: parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--loader-roughness')) || 0.2,
        autoHideDelay: 2000,
        minLoadTime: 1500,
        enableBobbing: true,
        enableTilting: true,
        enablePulsing: true,
        pauseOnHover: true,
    };

    // State
    let scene, camera, renderer, loader3D, animationId;
    let isHovered = false;
    let isPaused = false;
    let progress = 0;
    let startTime = Date.now();

    /**
     * Initialize 3D Loader
     */
    function init3DLoader() {
        const canvas = document.getElementById('loader-canvas');
        const container = document.getElementById('loader-canvas-container');
        
        if (!canvas || !container) {
            console.error('Loader canvas or container not found');
            return;
        }

        // Check WebGL support
        if (!isWebGLAvailable()) {
            console.warn('WebGL not supported, using fallback');
            useFallbackLoader();
            return;
        }
        
        try {
            // Scene
            scene = new THREE.Scene();
            
            // Camera
            camera = new THREE.PerspectiveCamera(
                45,
                container.clientWidth / container.clientHeight,
                0.1,
                1000
            );
            camera.position.z = 5;
            
            // Renderer
            renderer = new THREE.WebGLRenderer({
                canvas: canvas,
                antialias: detectHighPerformanceDevice(),
                alpha: true,
                powerPreference: 'high-performance'
            });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
            renderer.toneMapping = THREE.ACESFilmicToneMapping;
            renderer.toneMappingExposure = 1.2;
            
            // Create 3D Loader Object
            createLoaderObject();
            
            // Lighting
            setupLighting();
            
            // Events
            setupEvents(canvas);
            
            // Start Animation
            animate();
            
        } catch (error) {
            console.error('Error initializing 3D loader:', error);
            useFallbackLoader();
        }
    }
    
    /**
     * Create 3D Loader Object (Torus Knot)
     */
    function createLoaderObject() {
        // Geometry - Adjust complexity based on device
        const complexity = detectHighPerformanceDevice() ? [128, 32] : [64, 16];
        const geometry = new THREE.TorusKnotGeometry(1, 0.3, complexity[0], complexity[1], 2, 3);
        
        // Material - Hybrid Metallic + Translucent
        const material = new THREE.MeshPhysicalMaterial({
            color: new THREE.Color(CONFIG.colorHex),
            metalness: CONFIG.metalness,
            roughness: CONFIG.roughness,
            transmission: 0.3,
            thickness: 0.5,
            clearcoat: 1.0,
            clearcoatRoughness: 0.1,
            envMapIntensity: 1.5,
            side: THREE.DoubleSide
        });
        
        loader3D = new THREE.Mesh(geometry, material);
        scene.add(loader3D);
        
        // Environment Map
        const envMap = createEnvironmentMap();
        scene.environment = envMap;
        material.envMap = envMap;
    }
    
    /**
     * Setup Lighting
     */
    function setupLighting() {
        // Ambient Light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
        scene.add(ambientLight);
        
        // Directional Light 1 (Primary Color)
        const directionalLight1 = new THREE.DirectionalLight(CONFIG.colorHex, 2);
        directionalLight1.position.set(5, 5, 5);
        scene.add(directionalLight1);
        
        // Directional Light 2 (Accent Color)
        const directionalLight2 = new THREE.DirectionalLight(CONFIG.accentColor, 1.5);
        directionalLight2.position.set(-5, -5, 5);
        scene.add(directionalLight2);
        
        // Point Light
        const pointLight = new THREE.PointLight(0xffffff, 1, 100);
        pointLight.position.set(0, 0, 10);
        scene.add(pointLight);
    }
    
    /**
     * Create Simple Environment Map
     */
    function createEnvironmentMap() {
        const size = 512;
        const canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');
        
        // Create gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, size);
        gradient.addColorStop(0, CONFIG.accentColor);
        gradient.addColorStop(1, CONFIG.colorHex);
        ctx.fillStyle = gradient;
        ctx.fillRect(0, 0, size, size);
        
        const texture = new THREE.CanvasTexture(canvas);
        const cubeRenderTarget = new THREE.WebGLCubeRenderTarget(size);
        
        return cubeRenderTarget.texture;
    }
    
    /**
     * Setup Events
     */
    function setupEvents(canvas) {
        if (CONFIG.pauseOnHover) {
            canvas.addEventListener('mouseenter', () => {
                isHovered = true;
            });
            
            canvas.addEventListener('mouseleave', () => {
                isHovered = false;
            });
        }
        
        // Handle Resize
        window.addEventListener('resize', onWindowResize);
        
        // Handle Page Load
        window.addEventListener('load', onPageLoad);
    }
    
    /**
     * Animation Loop
     */
    function animate() {
        animationId = requestAnimationFrame(animate);
        
        if (loader3D && !isPaused) {
            const time = Date.now() * 0.001;
            
            // Smooth rotation
            if (!isHovered) {
                loader3D.rotation.x += 0.005 * CONFIG.rotationSpeed;
                loader3D.rotation.y += 0.008 * CONFIG.rotationSpeed;
            }
            
            // Subtle bobbing
            if (CONFIG.enableBobbing) {
                loader3D.position.y = Math.sin(time * 0.5) * 0.1;
            }
            
            // Subtle tilting
            if (CONFIG.enableTilting) {
                loader3D.rotation.z = Math.sin(time * 0.3) * 0.1;
            }
            
            // Pulse scale
            if (CONFIG.enablePulsing) {
                const scale = 1 + Math.sin(time * 2) * 0.05;
                loader3D.scale.set(scale, scale, scale);
            }
        }
        
        renderer.render(scene, camera);
    }
    
    /**
     * Handle Window Resize
     */
    function onWindowResize() {
        const container = document.getElementById('loader-canvas-container');
        if (!container || !camera || !renderer) return;
        
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    }
    
    /**
     * Handle Page Load
     */
    function onPageLoad() {
        const loadTime = Date.now() - startTime;
        const remainingTime = Math.max(0, CONFIG.minLoadTime - loadTime);
        
        setTimeout(() => {
            progress = 100;
            updateProgress(100);
            setTimeout(hideLoader, 500);
        }, remainingTime);
    }
    
    /**
     * Simulate Loading Progress
     */
    function simulateLoading() {
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            
            if (progress >= 95) {
                progress = 95; // Stop at 95%, wait for actual page load
                clearInterval(interval);
            }
            
            updateProgress(progress);
        }, 200);
    }
    
    /**
     * Update Progress Bar
     */
    function updateProgress(value) {
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        
        if (progressBar) {
            progressBar.style.width = Math.round(value) + '%';
        }
        
        if (progressPercentage) {
            progressPercentage.textContent = Math.round(value) + '%';
        }
    }
    
    /**
     * Hide Loader
     */
    function hideLoader() {
        const loadingScreen = document.getElementById('loading-screen');
        const mainContent = document.getElementById('main-content');
        
        if (!loadingScreen) return;
        
        // Pause animation
        isPaused = true;
        
        // Fade out
        loadingScreen.style.opacity = '0';
        loadingScreen.style.visibility = 'hidden';
        
        // Show main content
        setTimeout(() => {
            if (mainContent) {
                mainContent.style.display = 'block';
            }
            
            // Cancel animation frame
            if (animationId) {
                cancelAnimationFrame(animationId);
            }
            
            // Cleanup
            cleanup();
            
            // Dispatch event
            window.dispatchEvent(new Event('loaderHidden'));
        }, 800);
    }
    
    /**
     * Cleanup Resources
     */
    function cleanup() {
        if (renderer) {
            renderer.dispose();
        }
        
        if (loader3D) {
            if (loader3D.geometry) loader3D.geometry.dispose();
            if (loader3D.material) loader3D.material.dispose();
        }
        
        if (scene) {
            scene.traverse((object) => {
                if (object.geometry) object.geometry.dispose();
                if (object.material) {
                    if (Array.isArray(object.material)) {
                        object.material.forEach(material => material.dispose());
                    } else {
                        object.material.dispose();
                    }
                }
            });
        }
    }
    
    /**
     * Check WebGL Availability
     */
    function isWebGLAvailable() {
        try {
            const canvas = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && 
                (canvas.getContext('webgl') || canvas.getContext('experimental-webgl')));
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Detect High Performance Device
     */
    function detectHighPerformanceDevice() {
        // Check for high-end GPU
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        
        if (!gl) return false;
        
        const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
        if (!debugInfo) return true; // Assume high-end if can't detect
        
        const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
        
        // Low-end GPUs
        const lowEndGPUs = ['Intel', 'Mali', 'Adreno 3', 'Adreno 4', 'PowerVR'];
        return !lowEndGPUs.some(gpu => renderer.includes(gpu));
    }
    
    /**
     * Fallback CSS Loader
     */
    function useFallbackLoader() {
        const container = document.getElementById('loader-canvas-container');
        if (!container) return;
        
        container.innerHTML = `
            <div style="width: 100px; height: 100px; border: 8px solid rgba(255,255,255,0.2); border-top-color: ${CONFIG.colorHex}; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <style>
                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            </style>
        `;
        
        // Still simulate loading
        simulateLoading();
    }
    
    /**
     * Initialize on DOM Ready
     */
    function initialize() {
        if (typeof THREE === 'undefined') {
            console.error('Three.js not loaded');
            useFallbackLoader();
            return;
        }
        
        init3DLoader();
        
        // Start simulated loading
        setTimeout(() => {
            simulateLoading();
        }, 500);
    }
    
    // Auto-initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    // Expose API
    window.Loader3D = {
        hide: hideLoader,
        updateProgress: updateProgress,
        config: CONFIG
    };
    
})();
