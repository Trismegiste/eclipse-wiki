import { deleteVertex } from './business';

describe('Wikitext autocomplete component', () => {

    const handoutTitle = 'Wikitext Autocomplete ' + Math.random()
    const linkTitle = 'Internal Link ' + Math.random()

    it('creates the first Handout', () => {
        cy.visit('/handout/create')
        cy.get('#handout_target').type('GM')
        cy.get('#handout_title').type(linkTitle)
        cy.get('#handout_pcInfo').type('nothing')
        cy.get('#handout_create').click()
    })

    it('links a 2nd Handout to the first with autocomplete + enter', () => {
        cy.visit('/handout/create')
        cy.get('#handout_title').type(handoutTitle)

        cy.intercept('/vertex/search?q=*').as('vertices')
        cy.get('#handout_pcInfo').type(`[[Intern{enter}`)
        cy.wait('@vertices')
        cy.wait(100)
        cy.get('#handout_pcInfo').should('have.value', `[[${linkTitle}]] `)
    })

    it('links a 2nd Handout to the first with autocomplete + select', () => {
        cy.visit('/handout/create')
        cy.get('#handout_title').type(handoutTitle)

        cy.intercept('/vertex/search?q=*').as('vertices')
        cy.get('#handout_pcInfo').type(`[[Intern`)
        cy.wait('@vertices')
        cy.wait(100)
        cy.get('select.autocomplete-combobox').should('be.visible')
        cy.get('select.autocomplete-combobox option').first().should('be.selected')
        cy.get('select.autocomplete-combobox option').first().click()
        cy.get('#handout_pcInfo').should('have.value', `[[${linkTitle}]] `)
    })

    it('deletes test Handout', () => {
        deleteVertex(linkTitle)
    })

})
