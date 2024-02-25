/*
 * Eclipse Wiki
 */

export function deleteVertex(toDelete) {
    cy.intercept('/vertex/filter*').as('listing')
    cy.visit('/vertex/list')
    cy.wait('@listing')
    cy.get('.pure-form input').type(toDelete)
    cy.get('.block-link h2').first().then(title => {
        if (title.text() === toDelete) {
            cy.get('.entity-listing .icon-trash-empty').first().click()
            cy.get('#form_delete').click()
        }
    })
}