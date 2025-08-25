import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { EffectComposer } from 'three/addons/postprocessing/EffectComposer.js';
import { RenderPass } from 'three/addons/postprocessing/RenderPass.js';
import { UnrealBloomPass } from 'three/addons/postprocessing/UnrealBloomPass.js';
import { CSS2DRenderer, CSS2DObject } from 'three/addons/renderers/CSS2DRenderer.js';

document.addEventListener('DOMContentLoaded', function() {
    
    // --- BIẾN TOÀN CỤC ---
    let scene, camera, renderer, controls, composer, stars;
    const orbitLines = [];
    let orbitsVisible = false;
    const clock = new THREE.Clock();
    const clickableObjects = [];
    const planetMeshes = {};
    const planetOrbitAngles = {};
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    const infoBox = document.getElementById('object-info-box');
    const textureLoader = new THREE.TextureLoader();
    let highlightMesh, selectedObject = null;
    let isAnimating = true;
    const playPauseButton = document.getElementById('playPauseButton');
    const toggleOrbitsButton = document.getElementById('toggleOrbitsButton');
    let sunMesh, moonMesh, sunMaterial;
    let moonOrbitAngle = 0;
    let labelRenderer;
    let stellarLayer1, stellarLayer2, stellarLayer3, galaxyLayer;
    
    // --- DỮ LIỆU ---
    const artisticRadii = { Sun: 3.0, Mercury: 0.42, Venus: 0.735, Earth: 0.7857, Mars: 0.525, Jupiter: 2.94, Saturn: 2.52, Uranus: 1.575, Neptune: 1.47, Moon: 0.7857 * 0.2726 };
    const planetData = [
        { name: "Sao Thủy", nameEng: "Mercury", texturePlaceholder: "saothuy.jpg", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2369/?fs=true", modelRadius: artisticRadii.Mercury, orbitalRadius: 10, orbitalSpeed: 0.0048, rotationSpeed: 0.002, info: "Hành tinh nhỏ nhất trong Hệ Mặt Trời và gần Mặt Trời nhất." },
        { name: "Sao Kim", nameEng: "Venus", texturePlaceholder: "saokim.jpg", apiModelPlaceholder:"https://solarsystem.nasa.gov/gltf_embed/2342/?fs=true", modelRadius: artisticRadii.Venus, orbitalRadius: 12.5, orbitalSpeed: 0.0035, rotationSpeed: 0.001, info: "Hành tinh nóng nhất do hiệu ứng nhà kính mạnh mẽ." },
        { name: "Trái Đất", nameEng: "Earth", texturePlaceholder: "traidat.jpg", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2393/?fs=true", modelRadius: artisticRadii.Earth, orbitalRadius: 19.5, orbitalSpeed: 0.0020, rotationSpeed: 0.005, info: "Ngôi nhà duy nhất của chúng ta, hành tinh duy nhất được biết có sự sống." },
        { name: "Sao Hỏa", nameEng: "Mars", texturePlaceholder: "saohoa.jpg", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2372/", modelRadius: artisticRadii.Mars, orbitalRadius: 26, orbitalSpeed: 0.0015, rotationSpeed: 0.0048, info: "Được mệnh danh là 'Hành tinh Đỏ' do bề mặt có màu đỏ của sắt oxit." },
        { name: "Sao Mộc", nameEng: "Jupiter", texturePlaceholder: "saomoc.jpg", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2375/?fs=true", modelRadius: artisticRadii.Jupiter, orbitalRadius: 33, orbitalSpeed: 0.0008, rotationSpeed: 0.012, info: "Hành tinh lớn nhất trong Hệ Mặt Trời, một hành tinh khí khổng lồ." },
        { name: "Sao Thổ", nameEng: "Saturn", texturePlaceholder: "saotho.jpg", ringTexturePlaceholder: "vongsaotho.png", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2355/?fs=true", modelRadius: artisticRadii.Saturn, orbitalRadius: 40, orbitalSpeed: 0.0005, rotationSpeed: 0.011, info: "Nổi tiếng với hệ thống vành đai ngoạn mục được tạo thành từ băng và đá." },
        { name: "Sao Thiên Vương", nameEng: "Uranus", texturePlaceholder: "saothienvuong.jpg", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2344/?fs=true", modelRadius: artisticRadii.Uranus, orbitalRadius: 50, orbitalSpeed: 0.0003, rotationSpeed: 0.006, info: "Hành tinh băng khổng lồ có trục quay nghiêng một cách kỳ lạ." },
        { name: "Sao Hải Vương", nameEng: "Neptune", texturePlaceholder: "saohaivuong.jpg", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2364/?fs=true", modelRadius: artisticRadii.Neptune, orbitalRadius: 60, orbitalSpeed: 0.0002, rotationSpeed: 0.0055, info: "Hành tinh xa Mặt Trời nhất, nổi tiếng với những cơn gió có tốc độ siêu thanh." }
    ];
    
    const stellarLayer1Data = [
        { name: "Sun", info: "Ngôi sao trung tâm của chúng ta, chiếm 99.8% khối lượng của Hệ Mặt Trời.", position: new THREE.Vector3(0,0,0), color: 0xFFFDE5, size: 20 },
        { name: "Alpha Centauri", info: "Hệ sao ba gần nhất với Mặt Trời, cách chúng ta khoảng 4.37 năm ánh sáng.", position: new THREE.Vector3(200, 80, -300), color: 0xFFFF99, size: 10 },
        { name: "Sirius", info: "Ngôi sao sáng nhất trên bầu trời đêm, còn được gọi là 'Sao Thiên Lang'.", position: new THREE.Vector3(-450, -100, 500), color: 0x99CCFF, size: 10 },
    ];
    const stellarLayer2Data = [
        { name: "Vega", info: "Một trong những ngôi sao sáng nhất trên bầu trời, thuộc chòm sao Thiên Cầm.", position: new THREE.Vector3(-2000, 1500, -3000), color: 0xA9D2FF, size: 15 },
        { name: "Arcturus", info: "Một ngôi sao khổng lồ đỏ và là một trong những ngôi sao sáng nhất có thể nhìn thấy từ Trái Đất.", position: new THREE.Vector3(1000, -500, 4000), color: 0xFF8C00, size: 15 },
    ];
    const stellarLayer3Data = [
        { name: "Pleiades Cluster", info: "Một cụm sao mở nổi tiếng trong chòm sao Kim Ngưu, còn được gọi là cụm sao Tua Rua.", position: new THREE.Vector3(-10000, 8000, -5000), color: 0x9BBFF9, size: 18 },
        { name: "Hyades Cluster", info: "Cụm sao mở gần nhất với Hệ Mặt Trời, có hình chữ V.", position: new THREE.Vector3(12000, -5000, 8000), color: 0xFFDDBB, size: 18 },
    ];
    
    function createStarTexture() {
        const canvas = document.createElement('canvas'); canvas.width = 128; canvas.height = 128;
        const context = canvas.getContext('2d');
        const gradient = context.createRadialGradient(64, 64, 0, 64, 64, 64);
        gradient.addColorStop(0, 'rgba(255,255,255,1)'); gradient.addColorStop(0.2, 'rgba(255,255,255,0.7)');
        gradient.addColorStop(0.6, 'rgba(255,255,255,0.1)'); gradient.addColorStop(1, 'rgba(255,255,255,0)');
        context.fillStyle = gradient; context.fillRect(0, 0, 128, 128);
        return new THREE.CanvasTexture(canvas);
    }

    function createStarfield(count, spread) {
        const starVertices = [];
        for (let i = 0; i < count; i++) {
            starVertices.push(THREE.MathUtils.randFloatSpread(spread), THREE.MathUtils.randFloatSpread(spread), THREE.MathUtils.randFloatSpread(spread));
        }
        const geometry = new THREE.BufferGeometry().setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
        const material = new THREE.PointsMaterial({ color: 0xffffff, size: spread / 8000, transparent: true, opacity: 0.7 });
        return new THREE.Points(geometry, material);
    }

    function createStellarLayer(layerData, backgroundSpread) {
        const starTexture = createStarTexture();
        const layerGroup = new THREE.Group();
        layerData.forEach(starData => {
            const starPoint = new THREE.Points(
                new THREE.BufferGeometry().setAttribute('position', new THREE.Float32BufferAttribute([0,0,0], 3)),
                new THREE.PointsMaterial({ map: starTexture, color: starData.color, size: starData.size, blending: THREE.AdditiveBlending, depthWrite: false, transparent: true, sizeAttenuation: false })
            );
            
            const starDiv = document.createElement('div');
            starDiv.className = 'star-label';
            starDiv.textContent = starData.name;
            starDiv.style.pointerEvents = 'auto';
            starDiv.style.cursor = 'pointer';
            starDiv.addEventListener('click', (event) => {
                event.stopPropagation();
                displayInfo(starData);
                highlightMesh.visible = false;
            });

            const starLabel = new CSS2DObject(starDiv);
            starLabel.position.set(0, starData.size * 6.5, 0); 
            
            const starGroup = new THREE.Group();
            starGroup.add(starPoint, starLabel);
            starGroup.position.copy(starData.position);
            layerGroup.add(starGroup);
        });
        layerGroup.add(createStarfield(5000, backgroundSpread));
        layerGroup.visible = false;
        scene.add(layerGroup);
        return layerGroup;
    }

   function createParticleGalaxy() {
        galaxyLayer = new THREE.Group();
        const numStars = 50000;
        const galaxyRadius = 18000;
        const numArms = 5;
        const spinFactor = 0.0008;
        const randomness = 3000;
        const thickness = 100;
        const particleSize = 40;
        const opacity = 0.9;
        const colorCore = new THREE.Color(0xffffff);
        const colorArm1 = new THREE.Color(0x99ccff);
        const colorArm2 = new THREE.Color(0xff88cc);
        const positions = []; 
        const colors = [];

        for(let i=0; i<numStars; i++) {
            const r = Math.random() * galaxyRadius;
            const spin = r * spinFactor;
            const branch = (i % numArms) * (Math.PI * 2 / numArms);
            const armRandomness = randomness * (r / galaxyRadius);
            const randomX = Math.pow(Math.random(), 2) * (Math.random() < 0.5 ? 1 : -1) * armRandomness;
            const randomZ = Math.pow(Math.random(), 2) * (Math.random() < 0.5 ? 1 : -1) * armRandomness;
            const x = Math.cos(branch + spin) * r + randomX;
            const z = Math.sin(branch + spin) * r + randomZ;
            const y = (Math.random() - 0.5) * thickness;
            positions.push(x, y, z);
            const mixedColor = colorCore.clone();
            const lerpColor = i % 2 === 0 ? colorArm1 : colorArm2;
            mixedColor.lerp(lerpColor, r / galaxyRadius);
            colors.push(mixedColor.r, mixedColor.g, mixedColor.b);
        }

        const geometry = new THREE.BufferGeometry();
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
        const material = new THREE.PointsMaterial({ 
            size: particleSize, 
            sizeAttenuation: true, 
            depthWrite: false, 
            blending: THREE.AdditiveBlending, 
            vertexColors: true, 
            transparent: true, 
            opacity: opacity 
        });

        const points = new THREE.Points(geometry, material);
        points.rotation.y = Math.random() * Math.PI * 2;
        galaxyLayer.add(points);
        galaxyLayer.visible = false;
        scene.add(galaxyLayer);
    }

    function init() {
        scene = new THREE.Scene();
        camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 150000);
        camera.position.set(0, 25, 65);
        camera.lookAt(0,0,0);
        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.body.appendChild(renderer.domElement);
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.2;
        const starVertices = [];
        for (let i = 0; i < 15000; i++) {
            const x = THREE.MathUtils.randFloatSpread(2000); const y = THREE.MathUtils.randFloatSpread(2000); const z = THREE.MathUtils.randFloatSpread(2000);
            starVertices.push(x, y, z);
        }
        const starGeometry = new THREE.BufferGeometry();
        starGeometry.setAttribute('position', new THREE.Float32BufferAttribute(starVertices, 3));
        const starMaterial = new THREE.PointsMaterial({ color: 0xffffff, size: 0.7, transparent: true, opacity: 0.8, sizeAttenuation: false });
        stars = new THREE.Points(starGeometry, starMaterial);
        scene.add(stars);
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);
        const pointLight = new THREE.PointLight(0xffffff, 2.8, 1500);
        scene.add(pointLight);
        if (playPauseButton) {
            playPauseButton.textContent = 'Dừng';
            playPauseButton.addEventListener('click', () => { 
                isAnimating = !isAnimating; 
                playPauseButton.textContent = isAnimating ? 'Dừng' : 'Quay'; 
            });
        }
        if (toggleOrbitsButton) {
            toggleOrbitsButton.textContent = 'Hiện Quỹ Đạo';
            toggleOrbitsButton.addEventListener('click', () => { 
                orbitsVisible = !orbitsVisible; 
                orbitLines.forEach(line => { line.visible = orbitsVisible; }); 
                toggleOrbitsButton.textContent = orbitsVisible ? 'Ẩn Quỹ Đạo' : 'Hiện Quỹ Đạo'; 
            });
        }
        const sunGeometry = new THREE.SphereGeometry(artisticRadii.Sun, 64, 64);
        const sunVertexShader = ` varying vec2 vUv; void main() { vUv = uv; gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0); } `;
        const sunFragmentShader = ` uniform float u_time; varying vec2 vUv; float random(vec2 st) { return fract(sin(dot(st.xy, vec2(12.9898, 78.233))) * 43758.5453123); } float noise(vec2 st) { vec2 i = floor(st); vec2 f = fract(st); float a = random(i); float b = random(i + vec2(1.0, 0.0)); float c = random(i + vec2(0.0, 1.0)); float d = random(i + vec2(1.0, 1.0)); vec2 u = f * f * (3.0 - 2.0 * f); return mix(a, b, u.x) + (c - a) * u.y * (1.0 - u.x) + (d - b) * u.y * u.x; } void main() { vec2 uv1 = vUv * 3.0 + vec2(u_time * 0.05, 0.0); vec2 uv2 = vUv * 4.5 + vec2(u_time * 0.08, u_time * 0.03); float n1 = noise(uv1); float n2 = noise(uv2); float combined_noise = n1 * 0.6 + n2 * 0.4; vec3 color1 = vec3(1.0, 0.5, 0.2); vec3 color2 = vec3(1.0, 0.8, 0.0); vec3 final_color = mix(color1, color2, combined_noise); gl_FragColor = vec4(final_color, 1.0); }`;
        sunMaterial = new THREE.ShaderMaterial({ uniforms: { u_time: { value: 0.0 } }, vertexShader: sunVertexShader, fragmentShader: sunFragmentShader, transparent: true });
        sunMesh = new THREE.Mesh(sunGeometry, sunMaterial);
        sunMesh.userData = { name: "Mặt Trời", info: "Ngôi sao trung tâm của Hệ Mặt Trời.", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2352/?fs=true" };
        scene.add(sunMesh);
        clickableObjects.push(sunMesh);
        pointLight.position.copy(sunMesh.position);
        const renderPass = new RenderPass(scene, camera);
        const bloomPass = new UnrealBloomPass(new THREE.Vector2(window.innerWidth, window.innerHeight), 1.5, 0.4, 0.85);
        bloomPass.threshold = 0.6; bloomPass.strength = 1.5;
        composer = new EffectComposer(renderer);
        composer.addPass(renderPass); composer.addPass(bloomPass);
        planetData.forEach(data => {
            const texturePath = `${BASE_URL}/modelhemattroi/${data.texturePlaceholder}`;
            const material = new THREE.MeshStandardMaterial({ map: textureLoader.load(texturePath), roughness: 0.8, metalness: 0.1, transparent: true });
            const planet = new THREE.Mesh(new THREE.SphereGeometry(data.modelRadius, 32, 32), material);
            planet.userData = { name: data.name, info: data.info, apiModelPlaceholder: data.apiModelPlaceholder };
            scene.add(planet);
            clickableObjects.push(planet);
            planetMeshes[data.nameEng] = planet;
            planetOrbitAngles[data.nameEng] = Math.random() * Math.PI * 2;
            const orbitGeometry = new THREE.BufferGeometry();
            const orbitMaterial = new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: 0.25 });
            const points = [];
            for (let i = 0; i <= 128; i++) { const theta = (i / 128) * Math.PI * 2; points.push(new THREE.Vector3(Math.cos(theta) * data.orbitalRadius, 0, Math.sin(theta) * data.orbitalRadius)); }
            orbitGeometry.setFromPoints(points);
            const orbitLine = new THREE.Line(orbitGeometry, orbitMaterial);
            orbitLine.visible = orbitsVisible;
            scene.add(orbitLine);
            orbitLines.push(orbitLine);
            if (data.nameEng === "Saturn" && data.ringTexturePlaceholder) {
                const ringTexturePath = `${BASE_URL}/modelhemattroi/${data.ringTexturePlaceholder}`;
                let ringMaterial = new THREE.MeshBasicMaterial({ map: textureLoader.load(ringTexturePath), side: THREE.DoubleSide, transparent: true, opacity: 0.9 });
                const ringMesh = new THREE.Mesh(new THREE.RingGeometry(data.modelRadius * 1.2, data.modelRadius * 2.2, 64), ringMaterial);
                ringMesh.rotation.x = Math.PI / 2.3;
                planet.add(ringMesh);
            }
        });
        const moonTexturePath = `${BASE_URL}/modelhemattroi/mattrang.jpg`;
        let moonMaterial = new THREE.MeshStandardMaterial({ map: textureLoader.load(moonTexturePath), roughness: 0.9, transparent: true });
        const moonGeometry = new THREE.SphereGeometry(artisticRadii.Moon, 32, 32);
        moonMesh = new THREE.Mesh(moonGeometry, moonMaterial);
        moonMesh.userData = { name: "Mặt Trăng", info: "Vệ tinh tự nhiên của Trái Đất.", apiModelPlaceholder: "https://solarsystem.nasa.gov/gltf_embed/2366/?fs=true" };
        scene.add(moonMesh);
        clickableObjects.push(moonMesh);
        highlightMesh = new THREE.Mesh(new THREE.SphereGeometry(1, 48, 48), new THREE.MeshBasicMaterial({ color: 0xffff00, transparent: true, opacity: 0.35, depthWrite: false }));
        highlightMesh.visible = false;
        scene.add(highlightMesh);

        controls = new OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.minDistance = 1;
        controls.maxDistance = 80000;
        
        renderer.domElement.addEventListener('click', onMouseClick, false);
        window.addEventListener('resize', onWindowResize, false);
        
        labelRenderer = new CSS2DRenderer();
        labelRenderer.setSize(window.innerWidth, window.innerHeight);
        labelRenderer.domElement.style.position = 'absolute';
        labelRenderer.domElement.style.top = '0px';
        labelRenderer.domElement.style.pointerEvents = 'none';
        document.body.appendChild(labelRenderer.domElement);
        
        stellarLayer1 = createStellarLayer(stellarLayer1Data, 4000);
        stellarLayer2 = createStellarLayer(stellarLayer2Data, 14000);
        stellarLayer3 = createStellarLayer(stellarLayer3Data, 35000);
        createParticleGalaxy();
        
        animate();
    }
    
    function onMouseClick(event) {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1; 
        mouse.y = - (event.clientY / window.innerHeight) * 2 + 1;
        raycaster.setFromCamera(mouse, camera);

        const intersects = raycaster.intersectObjects(clickableObjects);
        if (intersects.length > 0 && intersects[0].object.material.opacity > 0.5) {
            selectedObject = intersects[0].object;
            displayInfo(selectedObject.userData);
            highlightMesh.position.copy(selectedObject.position);
            const objectRadius = selectedObject.geometry.parameters.radius;
            highlightMesh.scale.setScalar(objectRadius * 1.30);
            highlightMesh.visible = true;
        } else { 
            hideInfo(); 
        }
    }

    function displayInfo(data) {
        if (data && data.name && data.info) {
            let infoHTML = `<h3>${data.name}</h3><p>${data.info}</p>`;
            if (data.apiModelPlaceholder) {
                 infoHTML += `<p style="margin-top:10px;"><strong>Mô hình 3D từ NASA:</strong></p><iframe src="${data.apiModelPlaceholder}" title="Mô hình 3D ${data.name}" style="width:100%; height:200px; border:1px solid #555; margin-top:5px;"></iframe>`;
            }
            infoBox.innerHTML = infoHTML; 
            infoBox.style.display = 'block';
        } else { 
            hideInfo(); 
        }
    }
    function hideInfo() {
        infoBox.innerHTML = ''; infoBox.style.display = 'none';
        if (highlightMesh) { highlightMesh.visible = false; }
        selectedObject = null;
    }
    function onWindowResize() {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
        composer.setSize(window.innerWidth, window.innerHeight);
        if (labelRenderer) labelRenderer.setSize(window.innerWidth, window.innerHeight);
    }

    function animate() {
        requestAnimationFrame(animate);
        if (isAnimating) {
            sunMaterial.uniforms.u_time.value = clock.getElapsedTime();
            planetData.forEach(data => {
                const planet = planetMeshes[data.nameEng];
                if (planet) {
                    planet.rotation.y += data.rotationSpeed;
                    planetOrbitAngles[data.nameEng] += data.orbitalSpeed;
                    planet.position.x = Math.cos(planetOrbitAngles[data.nameEng]) * data.orbitalRadius;
                    planet.position.z = Math.sin(planetOrbitAngles[data.nameEng]) * data.orbitalRadius;
                }
            });
            const earthMesh = planetMeshes["Earth"];
            if (earthMesh && moonMesh) {
                const moonOrbitRadius = artisticRadii.Earth * 2.0;
                moonMesh.rotation.y += 0.003;
                moonOrbitAngle += 0.01;
                moonMesh.position.x = earthMesh.position.x + Math.cos(moonOrbitAngle) * moonOrbitRadius;
                moonMesh.position.z = earthMesh.position.z + Math.sin(moonOrbitAngle) * moonOrbitRadius;
                moonMesh.position.y = earthMesh.position.y;
            }
            if (highlightMesh.visible && selectedObject && selectedObject.type === 'Mesh') {
                highlightMesh.position.copy(selectedObject.position);
            }
        }
        controls.update();
        if (stars) { stars.position.copy(camera.position); }
        const distance = controls.getDistance();
        const scale = (val, inMin, inMax, outMin, outMax) => Math.max(outMin, Math.min(outMax, (val - inMin) * (outMax - outMin) / (inMax - inMin) + outMin));
        const solarFade = { start: 150, end: 300 };
        const stellar1Fade = { inStart: 250, inEnd: 800, outStart: 3000, outEnd: 4000 };
        const stellar2Fade = { inStart: 3500, inEnd: 5000, outStart: 12000, outEnd: 14000 };
        const stellar3Fade = { inStart: 13000, inEnd: 16000, outStart: 30000, outEnd: 35000 };
        const galaxyFade = { inStart: 32000, inEnd: 40000, outStart: Infinity, outEnd: Infinity };
        const getOpacity = (fadeRule) => {
            if (!fadeRule) return 1; let opacity = 0;
            if(distance >= fadeRule.inStart && distance < fadeRule.outStart) {
                opacity = scale(distance, fadeRule.inStart, fadeRule.inEnd, 0, 1);
            } else if (distance >= fadeRule.outStart) {
                opacity = 1 - scale(distance, fadeRule.outStart, fadeRule.outEnd, 0, 1);
            }
            return opacity;
        };
        const solarOpacity = 1 - scale(distance, solarFade.start, solarFade.end, 0, 1);
        clickableObjects.forEach(obj => {
            if (obj.material) { obj.material.opacity = solarOpacity; }
            obj.traverse(child => { if(child.isMesh && child.material) child.material.opacity = solarOpacity; });
        });
        orbitLines.forEach(line => { if (line.material) line.material.opacity = solarOpacity * 0.25; });
        if (stars && stars.material) { stars.material.opacity = solarOpacity * 0.8; }
        [stellarLayer1, stellarLayer2, stellarLayer3, galaxyLayer].forEach((layer, index) => {
            if (!layer) return;
            const fades = [stellar1Fade, stellar2Fade, stellar3Fade, galaxyFade][index];
            const opacity = getOpacity(fades);
            layer.visible = opacity > 0.01;
            layer.traverse(c => {
                if (c.isCSS2DObject) c.element.style.opacity = opacity * 0.3; 
                else if (c.material) c.material.opacity = opacity;
            });
        });
        composer.render();
        if(labelRenderer) labelRenderer.render(scene, camera);
    }

    init();

    // --- LOGIC MENU TỪ FILE GỐC ---
    const menuToggle = document.getElementById('menu-toggle');
    const mainMenu = document.getElementById('main-menu');
    if (menuToggle && mainMenu) {
        menuToggle.addEventListener('click', function() {
            mainMenu.classList.toggle('is-open');
            if (mainMenu.classList.contains('is-open')) { this.innerHTML = '✖'; this.setAttribute('aria-label', 'Đóng menu');
            } else { this.innerHTML = '☰'; this.setAttribute('aria-label', 'Mở menu'); }
        });
        
    }
    const langToggle = document.getElementById('lang-toggle');
    if (langToggle) {
        langToggle.addEventListener('change', function() {
            if (this.checked) { window.location.href = '?lang=en'; } else { window.location.href = '?lang=vi'; }
        });
    }

    const dropdownItems = document.querySelectorAll('#main-menu .dropdown, #main-menu .dropdown2');
    dropdownItems.forEach(function(item) {
        const link = item.querySelector('a');
        link.addEventListener('click', function(event) {
            if (window.getComputedStyle(menuToggle).display !== 'none') {
                event.preventDefault(); item.classList.toggle('submenu-open');
            }
        });
    });

});