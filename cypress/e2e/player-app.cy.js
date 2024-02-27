import { deleteVertex } from './business';

describe('Player Log', () => {

    const handoutTitle = 'Handout Cypress'

    it('connects to the player log', () => {
        cy.visit('/player/log')
    })

    it('deletes test Handout if already existing', () => {
        deleteVertex(handoutTitle)
    })

    it('creates a Handout with a picture', () => {
        // search for a picture
        cy.visit('/picture/upload')
        cy.get('.mosaic figcaption').first().then(elem => {
            const linkToPicture = elem[0].textContent

            cy.visit('/handout/create')
            cy.get('#handout_target').type('GM')
            cy.get('#handout_title').type(handoutTitle)
            cy.get('#handout_pcInfo').type(linkToPicture)
            cy.get('#handout_create').click()
        })
    })

    it('show the Handout and pushes the picture the player log', () => {
        cy.visit('/wiki/' + handoutTitle)
        cy.get('main .parsed-wikitext img').parent().then(elem => {
            const pushUrl = elem[0].href

            // show the player log
            cy.visit('/player/log')
            cy.get('.icon-user-circle').click()
            cy.get('img[x-ref="picture"]').invoke('attr', 'src').should('match', /^\/img/)

            cy.request('post', pushUrl)
            cy.wait(200)
            cy.get('img[x-ref="picture"]').invoke('attr', 'src').should('match', /^data/)
            cy.get('.icon-picture').parents('li').first().should('have.class', 'new-content')
            cy.get('.icon-picture').click()
            cy.get('.icon-picture').parents('li').first().should('not.have.class', 'new-content')
        })
    })


})
