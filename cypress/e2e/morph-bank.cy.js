import { deleteVertex } from './business';

describe('Morph bank creation', () => {

    const vertexName = 'Resleeving center'

    it('deletes Place if already existing', () => {
        deleteVertex(vertexName)
    })

    it('creates the Place', () => {
        cy.visit('/place/create')

        cy.get('#place_title').type(vertexName)
        cy.get('#place_content').type('Inventory')
        cy.get('#place_create').click()
    })

    it('edits the place content', () => {
        cy.visit('/wiki/' + vertexName)
        cy.get('.big-title .icon-edit').click()

        cy.get('main .minitoolbar .icon-morph').click()
        cy.get('#place_append_morph_bank_morph_list').select('Huldre')
        cy.get('#place_append_morph_bank_append').click()
        cy.get('.parsed-wikitext table caption').should('contain', 'Banque de morphes')
        cy.get('.parsed-wikitext td').should('contain', 'Huldre')
    })
})