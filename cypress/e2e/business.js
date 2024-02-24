/*
 * Eclipse Wiki
 */

export function deleteVertex(toDelete) {
    cy.visit('/vertex/list')
    cy.get('.pure-form input').type(toDelete)
    cy.get('.block-link h2').first().then(title => {
        if (title.text() === toDelete) {
            cy.get('.entity-listing .icon-trash-empty').first().click()
            cy.get('#form_delete').click()
        }
    })
}