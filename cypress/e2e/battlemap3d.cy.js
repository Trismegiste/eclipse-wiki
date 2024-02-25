import { deleteVertex } from './business';

describe('Battlemap3d CRUD', () => {

    const vertexName = 'Place Cypress'

    it('deletes Place if already existing', () => {
        deleteVertex(vertexName)
    })

    it('creates the Place', () => {
        cy.visit('/place/create')

        cy.get('#place_title').type(vertexName)
        cy.get('#place_content').type('Battlemap')
        cy.get('#place_create').click()
    })

    it('edits battlemap parameters', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-hexagon').click()

        cy.get('#form_voronoiParam_side').type(50)
        cy.get('#form_voronoiParam_avgTilePerRoom').type(15)
        cy.get('#form_voronoiParam_erosionForHallway').click()
        cy.get('#form_voronoiParam_erodingMinRoomSize').type(15)
        cy.get('#form_voronoiParam_erodingMaxNeighbour_0').click()
        cy.get('#form_voronoiParam_container').select('STRAT_DOME')
        cy.get('#form_voronoiParam_horizontalLines').type(1)
        cy.get('#form_voronoiParam_verticalLines').type(1)
        cy.get('a.pure-button .icon-randomize').click()
        cy.get('#form_generate').click()

        cy.get('img[src^="/voronoi/generate/"]').should('exist')
    })

    it('adds textures to the battlemap', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-rgbcolor').click()

        cy.get('#form_voronoiParam_tileWeight_cluster-industry').type(5)
        cy.get('#form_voronoiParam_tileWeight_cluster-sleep').type(5)
        cy.get('#form_voronoiParam_tileWeight_cluster-entertainment').type(1)
        cy.get('#form_voronoiParam_minClusterPerTile_cluster-oxygen').type(3)
        cy.get('#form_voronoiParam_minClusterPerTile_cluster-energy').type(2)
        cy.get('#form_voronoiParam_minClusterPerTile_cluster-park').type(1)
        cy.get('#form_texture').click()
    })

    it('populates the battlemap', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-extra').click()

        cy.get('#form_voronoiParam_tilePopulation_cluster-sleep_npc option').then(nodes => {
            cy.get('#form_voronoiParam_tilePopulation_cluster-sleep_npc').select(Math.trunc(nodes.length * Math.random()))
        })
        cy.get('#form_voronoiParam_tilePopulation_cluster-sleep_tilePerNpc').type(4)
        cy.get('#form_populate').click()
    })

    it('checks statistics of the battlemap', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-info-circled').click()

        cy.get('th').contains('cluster-sleep').parent('tr').find('td:contains("%")')
    })

    it('launches 3D view', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-view3d').parents('a').then(link => {
            cy.visit(link[0].href)
            cy.wait(4000)
            cy.screenshot('Battlemap 3D', {overwrite: true})
        })
    })
})