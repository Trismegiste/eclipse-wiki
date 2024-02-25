/*
 * Eclipse Wiki
 */

export function deleteVertex(toDelete) {
    cy.visit('/vertex/list')

    cy.get('.entity-listing').children().then(children => {
        let found = children.find(`h2:contains("${toDelete}")`)
        if (found.length) {
            found.parents('.pure-g').first()
                    .find('.icon-trash-empty').first().click()
            cy.get('#form_delete').click()
        }
    })

}