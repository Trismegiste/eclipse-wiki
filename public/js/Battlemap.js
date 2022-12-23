/*
 * eclipse-wiki
 */

class Battlemap
{
    side;
    wallHeight;
    texture;
    scene;

    constructor(scene) {
        this.scene = scene
    }

    setCamera() {
        const camera = this.scene.getCameraByName('player-camera')
        camera.position = new BABYLON.Vector3(this.side / 2, this.side, -this.side / 2)
        camera.setTarget(new BABYLON.Vector3(this.side / 2, 0, -this.side / 2))
        camera.minZ = 0.01
        camera.maxZ = this.side * 2
        camera.fov = 60 / 180 * Math.PI
        // Then apply collisions and gravity to the active camera
        camera.checkCollisions = true;
        camera.applyGravity = true;
        //Set the ellipsoid around the camera (e.g. your player's size)
        camera.ellipsoid = new BABYLON.Vector3(0.1, this.wallHeight / 3, 0.1);
    }

    setLight() {
        // Creates a light, aiming 0,1,0 - to the sky
        const light = new BABYLON.HemisphericLight("light", new BABYLON.Vector3(0, 1, 0), this.scene)
        light.intensity = 1.5
    }

    declareGround() {
        // Ground templates
        this.texture.forEach((key) => {
            const tile = BABYLON.MeshBuilder.CreateDisc("hexagon-" + key, {tessellation: 6, radius: 2 / 3 - 0.01}, this.scene)
            tile.rotation.z = Math.PI / 6
            tile.rotation.x = Math.PI / 2
            tile.isVisible = false

            const myMaterial = new BABYLON.StandardMaterial('mat-ground-' + key, this.scene)
            myMaterial.diffuseTexture = new BABYLON.Texture("/texture/habitat/ground/" + key + ".webp", this.scene)
            tile.material = myMaterial
        })
    }

    declareWall() {
        // Wall templates
        this.texture.forEach((key) => {
            const wall = BABYLON.MeshBuilder.CreatePlane("wall-" + key, {width: 2 / 3, height: this.wallHeight})
            wall.position.y = this.wallHeight / 2
            wall.position.x = 2 / 3 * Math.cos(Math.PI / 6)
            wall.rotation.y = Math.PI / 2
            wall.isVisible = false

            const myMaterial = new BABYLON.StandardMaterial('mat-wall-' + key, this.scene)
            myMaterial.diffuseTexture = new BABYLON.Texture("/texture/habitat/wall/" + key + ".webp", this.scene)
            wall.material = myMaterial
        })
    }

    declareSelector() {
        // selector
        const groundSelector = BABYLON.MeshBuilder.CreateDisc("selector-ground", {tessellation: 6, radius: 2 / 3}, this.scene)
        groundSelector.rotation.z = Math.PI / 6
        groundSelector.rotation.x = Math.PI / 2
        groundSelector.position.y = 0.01
        groundSelector.isVisible = false
        const selectorMat = new BABYLON.StandardMaterial('mat-selector')
        selectorMat.diffuseColor = new BABYLON.Color3(1, 0, 0)
        selectorMat.alpha = 0.5
        groundSelector.material = selectorMat
        groundSelector.actionManager = new BABYLON.ActionManager(this.scene);
        groundSelector.actionManager.registerAction(
                new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPickTrigger, (e) => {
                    const camera = this.scene.getCameraByName('player-camera')
                    const target = e.meshUnderPointer.position.clone()
                    target.y = camera.position.y

                    const frameRate = 10
                    const moving = new BABYLON.Animation("moving", "position", frameRate, BABYLON.Animation.ANIMATIONTYPE_VECTOR3)
                    moving.setKeys([
                        {frame: 0, value: camera.position},
                        {frame: frameRate, value: target}
                    ])
                    camera.animations.push(moving)
                    this.scene.beginAnimation(camera, 0, frameRate)
                })
                )
    }

    declareDoor() {
        // Generic door
        const door = BABYLON.MeshBuilder.CreatePlane('door', {width: 2 / 3, height: this.wallHeight})
        door.position.y = this.wallHeight / 2
        door.position.x = 2 / 3 * Math.cos(Math.PI / 6)
        door.rotation.y = Math.PI / 2
        door.isVisible = false

        const doorMat = new BABYLON.StandardMaterial('mat-door', this.scene)
        doorMat.diffuseTexture = new BABYLON.Texture("/texture/habitat/door.webp", this.scene)
        door.material = doorMat
    }

    buildGrid() {
        // map token
        let spriteManager = {}
        this.npc.forEach((npc) => {
            spriteManager[npc.label] = new BABYLON.SpriteManager('token-' + npc.label, '/picture/get/' + npc.picture, 2000, 504)
        })

        // Grid of HexaCell
        this.grid.forEach((cell, k) => {
            const ground = this.scene.getMeshByName('hexagon-' + cell.obj.template).createInstance("ground" + k)
            ground.position.x = cell.x
            ground.position.z = -cell.y
            ground.checkCollisions = true

            ground.actionManager = new BABYLON.ActionManager(this.scene);
            ground.actionManager.registerAction(
                    new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPointerOverTrigger, (e) => {
                        const selector = this.scene.getMeshByName('selector-ground')
                        const current = e.meshUnderPointer
                        selector.isVisible = true
                        selector.position.x = current.position.x
                        selector.position.z = current.position.z
                    })
                    )

            for (let dir = 0; dir < 6; dir++) {
                if (cell.obj.wall[dir]) {
                    const handle = new BABYLON.TransformNode("handle" + k + '-' + dir)
                    const meshName = cell.obj.door[dir] ? 'door' : 'wall-' + cell.obj.template
                    const tmpWall = this.scene.getMeshByName(meshName).createInstance("wall-" + k + '-' + dir)
                    tmpWall.parent = handle
                    tmpWall.checkCollisions = !cell.obj.door[dir]
                    handle.rotation.y = -dir * Math.PI / 3
                    handle.position.x = cell.x
                    handle.position.z = -cell.y

                    // clickable door
                    if (cell.obj.door[dir]) {
                        tmpWall.actionManager = new BABYLON.ActionManager(this.scene);
                        tmpWall.actionManager.registerAction(new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPickTrigger, (e) => {
                            const current = e.meshUnderPointer
                            current.isVisible = false
                        }))
                    }
                }
            }

            // token if any
            if (cell.obj.npc) {
                const manager = spriteManager[cell.obj.npc.label]
                const npc = new BABYLON.Sprite("npc-" + k, manager)
                npc.width = 0.5
                npc.height = 0.5
                npc.position = new BABYLON.Vector3(cell.x, 0.75, -cell.y)
            }
        })
    }

    create() {
        this.setCamera()
        this.setLight()
        this.declareGround()
        this.declareWall()
        this.declareSelector()
        this.declareDoor()
        this.buildGrid()
    }
}

module.exports = Battlemap
