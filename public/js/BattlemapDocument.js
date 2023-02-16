/*
 * Eclipse Wiki
 */

/**
 * The model for running the battlemap scene
 */
class BattlemapDocument
{
    // the battle map document
    theme
    side
    npc = []
    wallHeight
    texture = []
    grid = []

    // current status
    viewMode = 'fps'
    selectedOnTileIndex = null
    populateWithNpc = null
}
