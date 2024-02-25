describe('Quick NPC creation', () => {
    it('lands on home page', () => {
        cy.visit('/')
    })

    it('lauches a quick npc creation', () => {
        cy.visit('/')
        cy.get('main .icon-npcgraph').click()
        cy.get('.node-selection article h2').contains('root')
        cy.get('.node-selection footer label').contains('homme').click()
        cy.get('.node-selection footer label').contains('inner').click()
        cy.get('.node-selection footer label').contains('ruche').click()
        cy.get('.node-selection footer label').contains('social').click()
        cy.get('.node-selection footer label').contains('espion').click()
        cy.get('main form a').contains('Random name').click()

        cy.get('.result-selection h2').contains('Background').parents('div').first().find('ul > li > input[type=radio]').first().click()
        cy.get('.result-selection h2').contains('Faction').parents('div').first().find('ul > li > input[type=radio]').first().click()
        cy.get('.result-selection h2').contains('Morphe').parents('div').first().find('ul > li > input[type=radio]').first().click()
        cy.get('.result-selection h2').contains('Keywords').parents('div').first().find('ul > li > input[type=checkbox]').click({multiple: true})

        cy.intercept('/invokeai/ajax/local/*').as('getAvatar')
        cy.get('.result-selection h2').contains('Keywords').parents('div').first().find('ul > li > input[type=checkbox]').first().click()
        cy.wait('@getAvatar')
        cy.get('.avatar-suggest img').first().click()

        cy.get('#selector_generate').click()
        cy.get('.big-title .icon-eye').click()
    })

    it('shows the quick npc', () => {
        cy.intercept('/vertex/filter*').as('listing')
        cy.visit('/vertex/list')
        cy.wait('@listing')
        cy.wait(200)
        cy.get('main form input[type=text]').type('{enter}')

        // profile pic
        cy.get('img[src^="/picture/get/token"').should('exist')
        cy.get('img[src^="/profile/unique/"]').click()
        cy.get('.notif .flash-success').contains('sent')

        // visual
        cy.get('.parsed-wikitext a[href^="/picture/push"]').click()
        cy.get('section.notif div.flash-success').should('have.length', 2)
    })
})