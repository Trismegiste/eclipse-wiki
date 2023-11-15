/*
 * eclipse-wiki
 */

import { BattlemapDocument } from 'battlemap-document';
import { BattlemapBuilder } from 'battlemap-builder';

export default {
    extensions: ".battlemap",
    importMesh: function (meshesNames, scene, data, rootUrl, meshes, particleSystems, skeletons) {
        return true
    },
    loadAssets(scene, data, rootUrl) {
        var container = new AssetContainer(scene)
        return container
    },
    load: function (scene, data, rootUrl) {
        const battlemap = Object.assign(new BattlemapDocument(), JSON.parse(data))
        scene.metadata = battlemap

        const builder = new BattlemapBuilder(scene)
        builder.create()

        return true
    }
}
