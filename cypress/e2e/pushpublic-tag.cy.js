import { deleteVertex } from './business';

describe('Push Public Tag block', () => {

    const vertexName = 'Spaceship example'

    it('deletes Place if already existing', () => {
        deleteVertex(vertexName)
    })

    it('creates the Place', () => {
        cy.visit('/place/create')

        cy.get('#place_title').type(vertexName)
        cy.get('#place_content').type('Description')
        cy.get('#place_create').click()
    })

    it('edits the place content', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-edit').click()

        cy.get('#place_content').type('{selectAll}')
        cy.get('main .minitoolbar .icon-blockquote').click()
        cy.get('#place_create').click()
        cy.get('.parsed-wikitext blockquote').should('contain', 'Description')
    })

    it('checks the content', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.parsed-wikitext blockquote').should('contain', 'Description')
        cy.get('.parsed-wikitext blockquote i').should('have.class', 'icon-push')
    })
})