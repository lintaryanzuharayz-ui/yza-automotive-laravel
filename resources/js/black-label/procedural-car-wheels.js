import * as THREE from 'three';

export function createWheel(materials, { x, z, side }) {
    const wheel = new THREE.Group();
    wheel.position.set(x, .62, z);
    wheel.rotation.y = Math.PI / 2;

    const tire = new THREE.Mesh(new THREE.TorusGeometry(.49, .14, 18, 48), materials.rubber);
    tire.scale.set(1, 1, .88); tire.castShadow = true; tire.receiveShadow = true; wheel.add(tire);
    const rim = new THREE.Mesh(new THREE.CylinderGeometry(.36, .36, .17, 32), materials.wheel);
    rim.castShadow = true; wheel.add(rim);
    const hub = new THREE.Mesh(new THREE.CylinderGeometry(.09, .09, .2, 20), materials.blackGloss);
    hub.castShadow = true; wheel.add(hub);
    const disc = new THREE.Mesh(new THREE.CylinderGeometry(.27, .27, .08, 32), materials.brake);
    disc.position.x = side * .1; wheel.add(disc);
    for (let index = 0; index < 10; index += 1) {
        const spoke = new THREE.Mesh(new THREE.BoxGeometry(.055, .3, .06), materials.wheel);
        spoke.position.x = side * .11;
        spoke.rotation.z = (index / 10) * Math.PI * 2;
        wheel.add(spoke);
    }
    const caliper = new THREE.Mesh(new THREE.BoxGeometry(.12, .25, .12), materials.brake);
    caliper.position.set(side * .11, .15, .25); wheel.add(caliper);
    return wheel;
}
