import * as THREE from 'three';
import { createCarMaterials, disposeMaterials } from './procedural-car-materials.js';
import { createWheel } from './procedural-car-wheels.js';

function makeSurface(materials) {
    const stations = [
        [-3.25, .56, .82], [-2.85, .76, 1.18], [-2.15, .93, 1.34], [-1.25, 1.0, 1.42],
        [-.25, 1.02, 1.46], [.9, .96, 1.48], [1.85, .9, 1.4], [2.65, .78, 1.22], [3.12, .56, .86],
    ];
    const profile = [[-.58, -.56], [-.82, -.28], [-.88, .1], [-.68, .42], [-.28, .62], [.28, .62], [.68, .42], [.88, .1], [.82, -.28], [.58, -.56]];
    const vertices = [];
    stations.forEach(([x, y, width]) => profile.forEach(([py, pz]) => vertices.push(x, y + py * .58, pz * width)));
    const indices = [];
    const ring = profile.length;
    for (let row = 0; row < stations.length - 1; row += 1) {
        for (let col = 0; col < ring - 1; col += 1) {
            const a = row * ring + col; const b = a + ring;
            indices.push(a, b, a + 1, b, b + 1, a + 1);
        }
    }
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute('position', new THREE.Float32BufferAttribute(vertices, 3));
    geometry.setIndex(indices); geometry.computeVertexNormals();
    const body = new THREE.Mesh(geometry, materials.body);
    body.castShadow = true; body.receiveShadow = true;
    return body;
}

function extrudedShape(points, depth, material, bevel = .04) {
    const shape = new THREE.Shape();
    points.forEach(([x, y], index) => index === 0 ? shape.moveTo(x, y) : shape.lineTo(x, y));
    shape.closePath();
    const geometry = new THREE.ExtrudeGeometry(shape, { depth, bevelEnabled: true, bevelSegments: 2, bevelSize: bevel, bevelThickness: bevel, curveSegments: 8 });
    geometry.translate(0, 0, -depth / 2); geometry.computeVertexNormals();
    return new THREE.Mesh(geometry, material);
}

function createCabin(materials) {
    const cabin = new THREE.Group();
    const roof = extrudedShape([[-1.5, .95], [-.85, 1.72], [.2, 1.92], [1.35, 1.62], [1.75, .95]], 1.72, materials.bodyHighlight, .08);
    roof.position.y = .12; roof.castShadow = true; cabin.add(roof);
    const glass = extrudedShape([[-1.25, 1.08], [-.75, 1.6], [.15, 1.74], [1.22, 1.52], [1.42, 1.08]], 1.76, materials.glass, .025);
    glass.position.set(0, .13, .12); cabin.add(glass);
    const windshield = extrudedShape([[-.7, 1.58], [.12, 1.73], [.28, 1.2], [-.82, 1.2]], .035, materials.glass, .01);
    windshield.position.set(0, .14, 1.01); windshield.rotation.x = -.06; cabin.add(windshield);
    const dash = new THREE.Mesh(new THREE.BoxGeometry(1.2, .18, 1.2), materials.interior);
    dash.position.set(.1, 1.02, 0); cabin.add(dash);
    [-.55, .55].forEach((z) => {
        const seat = new THREE.Mesh(new THREE.BoxGeometry(.58, .45, .48), materials.interior);
        seat.position.set(.55, .98, z); seat.rotation.z = -.08; seat.castShadow = true; cabin.add(seat);
    });
    return cabin;
}

function createDetails(materials) {
    const details = new THREE.Group();
    const grille = new THREE.Mesh(new THREE.BoxGeometry(1.4, .26, .06), materials.blackGloss);
    grille.position.set(-3.1, .56, 0); grille.rotation.z = -.03; details.add(grille);
    [-.72, .72].forEach((z) => {
        const intake = new THREE.Mesh(new THREE.CylinderGeometry(.28, .34, .08, 24), materials.blackGloss);
        intake.rotation.z = Math.PI / 2; intake.position.set(-3.05, .45, z); details.add(intake);
        const headlight = new THREE.Mesh(new THREE.BoxGeometry(.42, .08, .22), materials.light);
        headlight.position.set(-3.2, .86, z); headlight.rotation.z = -.18; details.add(headlight);
        const tail = new THREE.Mesh(new THREE.BoxGeometry(.3, .08, .18), materials.tailLight);
        tail.position.set(3.08, .9, z); details.add(tail);
    });
    const splitter = new THREE.Mesh(new THREE.BoxGeometry(.22, .04, 2.8), materials.blackGloss);
    splitter.position.set(-3.28, .34, 0); details.add(splitter);
    const diffuser = new THREE.Mesh(new THREE.BoxGeometry(.2, .12, 2.7), materials.blackGloss);
    diffuser.position.set(3.18, .36, 0); details.add(diffuser);
    [-.62, .62].forEach((z) => {
        const exhaust = new THREE.Mesh(new THREE.CylinderGeometry(.13, .13, .16, 24), materials.wheel);
        exhaust.rotation.z = Math.PI / 2; exhaust.position.set(3.23, .42, z); details.add(exhaust);
        const mirror = new THREE.Mesh(new THREE.SphereGeometry(.13, 16, 12), materials.blackGloss);
        mirror.scale.set(1.45, .65, .7); mirror.position.set(-.72, 1.2, z * 1.2); details.add(mirror);
    });
    return details;
}

export function createProceduralSportsCar() {
    const materials = createCarMaterials();
    const car = new THREE.Group();
    car.name = 'YZA Original Performance Coupe';
    car.rotation.y = Math.PI / 4;
    car.scale.setScalar(.88);
    const body = makeSurface(materials); body.position.y = .9; car.add(body);
    const cabin = createCabin(materials); car.add(cabin);
    const details = createDetails(materials); car.add(details);
    [-1.9, 1.85].forEach((x) => [-1.37, 1.37].forEach((z) => car.add(createWheel(materials, { x, z, side: Math.sign(z) }))));
    const sideSkirt = new THREE.Mesh(new THREE.BoxGeometry(3.8, .09, .1), materials.blackGloss);
    sideSkirt.position.set(0, .48, 1.41); car.add(sideSkirt);
    const antenna = new THREE.Mesh(new THREE.CylinderGeometry(.025, .025, .3, 8), materials.blackGloss);
    antenna.position.set(1.05, 1.98, 0); antenna.rotation.z = -.25; car.add(antenna);
    car.traverse((node) => { if (node.isMesh) { node.castShadow = true; node.receiveShadow = true; } });
    car.userData.dispose = () => {
        car.traverse((node) => { if (node.geometry) node.geometry.dispose(); });
        disposeMaterials(materials);
    };
    return car;
}
