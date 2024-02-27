describe('Autofocus', () => {

    it('checks autofocus on creating new Place', () => {
        cy.visit('/place/create')
        cy.get('#place_title').should('have.focus')
    })

    it('checks autofocus on creating new Transhuman', () => {
        cy.visit('/npc/create')
        cy.get('#npc_title').should('have.focus')
    })

    it('checks autofocus on creating new Scene', () => {
        cy.visit('/scene/create')
        cy.get('#scene_create_title').should('have.focus')
    })

    it('checks autofocus on creating new Timeline', () => {
        cy.visit('/timeline/create')
        cy.get('#timeline_create_title').should('have.focus')
    })
})