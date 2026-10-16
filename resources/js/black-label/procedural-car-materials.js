import * as THREE from 'three';

export function createCarMaterials() {
    return {
        body: new THREE.MeshPhysicalMaterial({ color: 0xd51d2c, metalness: .78, roughness: .26, clearcoat: 1, clearcoatRoughness: .12 }),
        bodyHighlight: new THREE.MeshPhysicalMaterial({ color: 0xf33a46, metalness: .74, roughness: .22, clearcoat: 1, clearcoatRoughness: .1 }),
        blackGloss: new THREE.MeshPhysicalMaterial({ color: 0x151922, metalness: .72, roughness: .18, clearcoat: .8 }),
        glass: new THREE.MeshPhysicalMaterial({ color: 0x050912, metalness: .2, roughness: .16, transmission: 0, transparent: false, opacity: 1, clearcoat: .8 }),
        rubber: new THREE.MeshPhysicalMaterial({ color: 0x030405, metalness: .02, roughness: .76 }),
        wheel: new THREE.MeshPhysicalMaterial({ color: 0x20242b, metalness: .94, roughness: .22, clearcoat: .35 }),
        brake: new THREE.MeshPhysicalMaterial({ color: 0xd31224, metalness: .62, roughness: .25, clearcoat: .5 }),
        light: new THREE.MeshStandardMaterial({ color: 0xe8f5ff, emissive: 0x8fcfff, emissiveIntensity: 4.2, metalness: .1, roughness: .2 }),
        tailLight: new THREE.MeshStandardMaterial({ color: 0xff172c, emissive: 0xff0717, emissiveIntensity: 4.8, metalness: .12, roughness: .2 }),
        interior: new THREE.MeshPhysicalMaterial({ color: 0x111318, metalness: .12, roughness: .54 }),
    };
}

export function disposeMaterials(materials) {
    Object.values(materials).forEach((material) => material.dispose());
}
