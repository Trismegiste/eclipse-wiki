/*
 * Eclipse Wiki
 */

/**
 * The model for running the battlemap scene
 */
class BattlemapDocument
{
    // from the battle map
    theme
    side
    npc = []
    wallHeight
    texture = []

    // status
    viewMode = 'fps'
    selectedOnTileIndex = null
    populateWithNpc = null
}
