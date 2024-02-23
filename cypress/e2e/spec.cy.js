describe('Timeline', () => {
    let fixture
    beforeEach(() => {
        cy.fixture('timeline').then(c => fixture = c)
    })

    it('creates a timeline', () => {
        cy.visit('/')
        cy.contains('Eclipse Savage')
        cy.get('.icon-movie-roll').click()

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

        cy.get('.icon-edit').click()
        cy.get('.icon-newchild').first().click()
    })
})