import * as THREE from 'three';
import { createProceduralSportsCar } from './black-label/procedural-car.js';

const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

function createContactShadow() {
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 128;
    const context = canvas.getContext('2d');
    const gradient = context.createRadialGradient(64, 64, 2, 64, 64, 62);
    gradient.addColorStop(0, 'rgba(0,0,0,.72)');
    gradient.addColorStop(.45, 'rgba(0,0,0,.34)');
    gradient.addColorStop(1, 'rgba(0,0,0,0)');
    context.fillStyle = gradient;
    context.fillRect(0, 0, 128, 128);
    return new THREE.CanvasTexture(canvas);
}

function roundedMaterial(color, roughness = .34, metalness = .82) {
    return new THREE.MeshPhysicalMaterial({
        color,
        metalness,
        roughness,
        clearcoat: .7,
        clearcoatRoughness: .22,
    });
}

function initScene(container) {
    let renderer;
    try {
        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true, powerPreference: 'high-performance' });
    } catch (error) {
        container.classList.add('is-webgl-fallback');
        return () => {};
    }

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(28, 1, .1, 100);
    camera.position.set(4.8, 2.9, 9.6);
    camera.lookAt(0, .78, 0);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.7));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.08;
    renderer.domElement.setAttribute('aria-hidden', 'true');
    renderer.domElement.className = 'black-label-webgl';
    container.replaceChildren(renderer.domElement);

    scene.add(new THREE.HemisphereLight(0x9fa8b7, 0x090a0d, 1.6));
    const keyLight = new THREE.DirectionalLight(0xffffff, 3.1);
    keyLight.position.set(-4, 8, 5);
    keyLight.castShadow = true;
    keyLight.shadow.mapSize.set(1024, 1024);
    keyLight.shadow.camera.left = -5;
    keyLight.shadow.camera.right = 5;
    keyLight.shadow.camera.top = 5;
    keyLight.shadow.camera.bottom = -5;
    scene.add(keyLight);
    const redLight = new THREE.PointLight(0xff1b2d, 12, 8, 2);
    redLight.position.set(2, .3, 1.5);
    scene.add(redLight);
    const rimLight = new THREE.PointLight(0x9fb8ff, 8, 9, 2);
    rimLight.position.set(-3, 3, -2);
    scene.add(rimLight);

    const showroom = new THREE.Group();
    scene.add(showroom);
    const platformMaterial = roundedMaterial(0x101216, .26, .92);
    const trimMaterial = roundedMaterial(0x292d35, .2, .98);
    const ledMaterial = new THREE.MeshStandardMaterial({ color: 0xff1927, emissive: 0xff0b18, emissiveIntensity: 5.5, metalness: .2, roughness: .3 });
    const base = new THREE.Mesh(new THREE.CylinderGeometry(3.7, 3.95, .35, 96), platformMaterial);
    base.position.y = .18; base.castShadow = true; base.receiveShadow = true; showroom.add(base);
    const upper = new THREE.Mesh(new THREE.CylinderGeometry(3.25, 3.55, .22, 96), trimMaterial);
    upper.position.y = .47; upper.castShadow = true; upper.receiveShadow = true; showroom.add(upper);
    const top = new THREE.Mesh(new THREE.CylinderGeometry(2.95, 3.18, .12, 96), platformMaterial);
    top.position.y = .64; top.receiveShadow = true; showroom.add(top);
    const ledRing = (radius, y, tube) => {
        const ring = new THREE.Mesh(new THREE.TorusGeometry(radius, tube, 12, 128), ledMaterial);
        ring.rotation.x = Math.PI / 2; ring.position.y = y; ring.castShadow = false; showroom.add(ring); return ring;
    };
    ledRing(3.42, .37, .045); ledRing(3.05, .7, .038);
    for (let index = 0; index < 12; index += 1) {
        const detail = new THREE.Mesh(new THREE.BoxGeometry(.16, .045, .32), trimMaterial);
        const angle = (index / 12) * Math.PI * 2;
        detail.position.set(Math.cos(angle) * 3.67, .39, Math.sin(angle) * 3.67);
        detail.rotation.y = -angle;
        showroom.add(detail);
    }
    const shadow = new THREE.Mesh(new THREE.PlaneGeometry(5.5, 2.7), new THREE.MeshBasicMaterial({ map: createContactShadow(), transparent: true, depthWrite: false }));
    shadow.rotation.x = -Math.PI / 2; shadow.position.y = .72; shadow.scale.set(1.2, .62, 1); showroom.add(shadow);

    const proceduralCar = createProceduralSportsCar();
    proceduralCar.position.y = .66;
    showroom.add(proceduralCar);
    container.classList.add('has-procedural-vehicle');
    const fallbackImage = container.parentElement?.querySelector('.hero-360-car');
    fallbackImage?.classList.add('is-hidden');
    fallbackImage?.style.setProperty('display', 'none', 'important');
    container.dispatchEvent(new CustomEvent('blacklabel:procedural-model-loaded'));

    const clock = new THREE.Clock();
    let raf = 0;
    let dragging = false;
    let lastX = 0;
    let velocity = 0;
    const onPointerDown = (event) => { dragging = true; lastX = event.clientX; container.setPointerCapture?.(event.pointerId); };
    const onPointerMove = (event) => { if (!dragging) return; velocity += (event.clientX - lastX) * .007; lastX = event.clientX; };
    const onPointerUp = () => { dragging = false; };
    container.addEventListener('pointerdown', onPointerDown);
    container.addEventListener('pointermove', onPointerMove);
    container.addEventListener('pointerup', onPointerUp);
    container.addEventListener('pointercancel', onPointerUp);
    let resetButton = container.parentElement?.querySelector('[data-black-label-reset]');
    if (!resetButton && container.parentElement) {
        resetButton = document.createElement('button');
        resetButton.type = 'button';
        resetButton.className = 'hero-3d-reset';
        resetButton.dataset.blackLabelReset = '';
        resetButton.textContent = 'Reset view';
        resetButton.setAttribute('aria-label', 'Reset tampilan kendaraan 3D');
        container.parentElement.appendChild(resetButton);
    }
    const resize = () => {
        const width = Math.max(1, container.clientWidth);
        const height = Math.max(1, container.clientHeight);
        const compact = width < 700;
        camera.position.set(compact ? -2.8 : -4.8, compact ? 2.45 : 2.9, compact ? 8.2 : 9.6);
        camera.fov = compact ? 34 : 28;
        camera.lookAt(0, compact ? .72 : .78, 0);
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height, false);
    };
    const resetView = () => { showroom.rotation.set(0, 0, 0); velocity = 0; resize(); };
    resetButton?.addEventListener('click', resetView);
    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(container);
    resize();
    const animate = () => {
        const delta = Math.min(clock.getDelta(), .05);
        if (!dragging && !reducedMotion) velocity += delta * .012;
        velocity *= Math.pow(.06, delta);
        showroom.rotation.y += velocity;
        renderer.render(scene, camera);
        raf = requestAnimationFrame(animate);
    };
    animate();

    return () => {
        cancelAnimationFrame(raf); resizeObserver.disconnect();
        container.removeEventListener('pointerdown', onPointerDown); container.removeEventListener('pointermove', onPointerMove);
        container.removeEventListener('pointerup', onPointerUp); container.removeEventListener('pointercancel', onPointerUp);
        proceduralCar.userData.dispose?.();
        scene.traverse((node) => { if (node.geometry) node.geometry.dispose(); if (node.material) { const materials = Array.isArray(node.material) ? node.material : [node.material]; materials.forEach((material) => { material.map?.dispose(); material.dispose(); }); } });
        resetButton?.removeEventListener('click', resetView);
        renderer.dispose(); container.replaceChildren();
    };
}

document.querySelectorAll('[data-black-label-scene]').forEach((container) => {
    const cleanup = initScene(container);
    container._blackLabelCleanup = cleanup;
    window.addEventListener('pagehide', cleanup, { once: true });
});
