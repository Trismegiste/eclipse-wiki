function deleteVertex(toDelete) {
    cy.visit('/vertex/list')
    cy.get('.pure-form input').type(toDelete)
    cy.get('.block-link h2').first().then(title => {
        if (title.text() === toDelete) {
            cy.get('.entity-listing .icon-trash-empty').first().click()
            cy.get('#form_delete').click()
        }
    })
}

describe('Full scenario', () => {
    let fixture
    beforeEach(() => {
        cy.fixture('timeline').then(c => fixture = c)
    })

    it('deletes Timeline if already existing', () => {
        deleteVertex(fixture.title)
    })

    it('deletes Scene if already existing', () => {
        deleteVertex(fixture.act6)
    })

    it('deletes Transhuman if already existing', () => {
        deleteVertex(fixture.protagonist)
    })

    it('deletes Place if already existing', () => {
        deleteVertex(fixture.place)
    })

    it('creates a new Timeline', () => {
        // home
        cy.visit('/')
        cy.contains('Eclipse Savage')
        cy.get('.icon-movie-roll').click()

        // create timeline
        cy.get('#timeline_create_title').type(fixture.title)
        cy.get('#timeline_create_elevatorPitch').type(fixture.elevatorPitch)
        cy.get('#timeline_create_tree_0').type(fixture.act1)
        cy.get('#timeline_create_tree_1').type(fixture.act2)
        cy.get('#timeline_create_tree_2').type(fixture.act3)
        cy.get('#timeline_create_tree_3').type(fixture.act4)
        cy.get('#timeline_create_tree_4').type(fixture.act5)
        cy.get('#timeline_create_create').click()

        cy.get('title').contains(fixture.title)
        cy.get('.parsed-wikitext > ul > li').should('have.length', 5)
    })

    it('appends a node to the Timeline tree', () => {
        cy.visit('/wiki/' + fixture.title)
        cy.get('.icon-edit').click()
        cy.get('.icon-newchild').first().click()
        cy.get('button[data-index=6] > i.icon-edit').click()
        cy.get('span[data-index=6]').next('textarea').type('{selectAll}[[' + fixture.act6 + ']]')
        cy.get('span[data-index=6]').parent().find('.button-write .icon-edit').click()
        cy.get('#timeline_create').click()
        cy.get('.parsed-wikitext > ul > li').should('have.length', 6)
        cy.get('.parsed-wikitext > ul > li').last().contains('merge')
    })

    it('creates a new Scene', () => {
        cy.visit('/wiki/' + fixture.title)
        cy.get('.parsed-wikitext > ul > li').last().find('a').click()
        cy.get('main a .icon-video').click()
        cy.get('#scene_create_place').type(fixture.place)
        cy.get('#scene_create_npc_0').type(fixture.protagonist)
        cy.get('#scene_create_npc_1').type('Project 2501')
        cy.get('#scene_create_npc_2').type('Batou')
        cy.get('#scene_create_prerequisite').type('Tank disabled')
        cy.get('#scene_create_event').type('Philosophy')
        cy.get('#scene_create_outcome').type('Merging')
        cy.get('#scene_create_append_timeline').type('Ghost in the')
        cy.wait(300)
        cy.get('#scene_create_append_timeline').type('{downArrow}{enter}')

        cy.get('a').contains(fixture.protagonist).should('have.class', 'new')
        cy.get('a').contains(fixture.title).should('not.have.class', 'new')
    })

    it('creates the Transhuman', () => {
        cy.visit('/wiki/' + fixture.act6)
        cy.get('a').contains(fixture.protagonist).click()
        cy.get('main a .icon-user-plus').click()
        cy.get('.pure-form .icon-wildcard').click()
        cy.get('#npc_background').select(fixture.background)
        cy.get('#npc_faction').select(fixture.faction)
        cy.get('#npc_morph').select(fixture.morph)
        cy.get('#npc_generate').click()
        cy.get('.big-title').contains('PNJ : ' + fixture.protagonist)
    })

    it('edits the Transhuman', () => {
        cy.visit('/wiki/' + fixture.protagonist)
        cy.get('.big-title .icon-d6').click()
        cy.get('#single_node_choice_node').select('root')
        cy.get('#single_node_choice_apply').click()
        cy.get('#npc_stats_edit').click()
        cy.get('nav.backlinks ul li').should('have.length', 2)
    })

    it('creates the Place', () => {
        cy.visit('/wiki/' + fixture.act6)
        cy.get('a').contains(fixture.place).should('have.class', 'new').click()
        cy.get('main a .icon-place').click()
        cy.get('#place_content').type('Flooded')
        cy.get('#place_create').click()
        cy.get('.big-title').contains(fixture.place)
        cy.visit('/wiki/' + fixture.act6)
        cy.get('a').contains(fixture.place).should('not.have.class', 'new')
    })

    it('checks wiki links', () => {
        cy.visit('/wiki/' + fixture.title)
        cy.get('a').contains("Puppetmaster").should('have.class', 'new').click()
        cy.get('main a .icon-user-plus').click()
        cy.get('#npc_title').should('have.value', 'Project 2501')
    })
})