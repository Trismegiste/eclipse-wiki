/*
 * Eclipse Wiki
 */

/**
 * The model for running the battlemap scene
 */
export class BattlemapDocument
{
    // the battle map document
    theme
    side
    wallHeight
    npcToken = []
    texture = []
    grid = []

    // current status
    playerViewOnTileIndex = null
}
