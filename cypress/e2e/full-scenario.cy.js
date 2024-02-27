import { deleteVertex } from './business';

describe('Full scenario', () => {
    let fixture

    beforeEach(() => {
        cy.fixture('timeline').then(c => fixture = c)
    })

    it('lands on homepage', () => {
        cy.visit('/')
        cy.contains('Eclipse Savage')
    })

    it('deletes Timeline if already existing', () => {
        deleteVertex(fixture.title)
    })

    it('deletes Scene if already existing', () => {
        deleteVertex(fixture.act6)
    })

    it('deletes Transhuman if already existing', () => {
        deleteVertex(fixture.protagonist)
    })

    it('deletes Place if already existing', () => {
        deleteVertex(fixture.place)
    })

    it('creates a new Timeline', () => {
        // home
        cy.visit('/')
        cy.get('.icon-movie-roll').click()

        // create timeline
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
    })

    it('appends a node to the Timeline tree', () => {
        cy.visit('/wiki/' + fixture.title)
        cy.get('.icon-edit').click()
        cy.get('.icon-newchild').first().click()
        cy.get('button[data-index=6] > i.icon-edit').click()
        cy.get('span[data-index=6]').next('textarea').type('{selectAll}[[' + fixture.act6 + ']]')
        cy.get('span[data-index=6]').parent().find('.button-write .icon-edit').click()
        cy.get('#timeline_create').click()
        cy.get('.parsed-wikitext > ul > li').should('have.length', 6)
        cy.get('.parsed-wikitext > ul > li').last().contains('merge')
    })

    it('creates a new Scene', () => {
        cy.visit('/wiki/' + fixture.title)
        cy.get('.parsed-wikitext > ul > li').last().find('a').click()
        cy.get('main a .icon-video').click()
        cy.get('#scene_create_place').type(fixture.place)
        cy.get('#scene_create_npc_0').type(fixture.protagonist)
        cy.get('#scene_create_npc_1').type('Project 2501')
        cy.get('#scene_create_npc_2').type('Batou')
        cy.get('#scene_create_prerequisite').type('Tank disabled')
        cy.get('#scene_create_event').type('Philosophy')
        cy.get('#scene_create_outcome').type('Merging')
        cy.get('#scene_create_append_timeline').type('Ghost in the')
        cy.wait(300)
        cy.get('#scene_create_append_timeline').type('{downArrow}{enter}')

        cy.get('a').contains(fixture.protagonist).should('have.class', 'new')
        cy.get('a').contains(fixture.title).should('not.have.class', 'new')
    })

    it('creates the Transhuman', () => {
        cy.visit('/wiki/' + fixture.act6)
        cy.get('a').contains(fixture.protagonist).click()
        cy.get('main a .icon-user-plus').click()
        cy.get('.pure-form .icon-wildcard').click()

        cy.intercept('/npc/background/info*').as('getBackground')
        cy.get('#npc_background').select(fixture.background)
        cy.wait('@getBackground')

        cy.intercept('/npc/faction/info*').as('getFaction')
        cy.get('#npc_faction').select(fixture.faction)
        cy.wait('@getFaction')

        cy.intercept('/npc/morph/info*').as('getMorph')
        cy.get('#npc_morph').select(fixture.morph)
        cy.wait('@getMorph')

        cy.get('#npc_generate').click()
        cy.get('.big-title').contains('PNJ : ' + fixture.protagonist)
    })

    it('initializes the Transhuman', () => {
        cy.visit('/wiki/' + fixture.protagonist)
        cy.get('.big-title .icon-d6').click()
        cy.get('#single_node_choice_node').select('root')
        cy.get('#single_node_choice_apply').click()
        cy.get('#npc_stats_edit').click()
        cy.get('nav.backlinks ul li').should('have.length', 2)
    })

    it('edits economy of the Transhuman', () => {
        cy.visit('/wiki/' + fixture.protagonist)
        cy.get('.big-title .icon-d6').click()
        cy.get('#npc_stats_economy_economy_1').type("5")
        cy.get('#npc_stats_economy_economy_2').type("4")
        cy.get('#npc_stats_economy_economy_3').type("3")
        cy.get('#npc_stats_edit').click()
    })

    it('edits info of the Transhuman', () => {
        cy.visit('/wiki/' + fixture.protagonist)
        cy.get('.big-title .icon-edit').click()
        cy.get('#npc_info_content').type('a.k.a "Major" of the [[Section 9]]')
        cy.get('#npc_info_hashtag').type('#self-improvment')
        cy.get('#npc_info_create').click()

        cy.get('.parsed-wikitext a').should('have.class', 'new')
    })

    it('creates the Place', () => {
        cy.visit('/wiki/' + fixture.act6)
        cy.get('a').contains(fixture.place).should('have.class', 'new').click()
        cy.get('main a .icon-place').click()
        cy.get('#place_content').type('Flooded')
        cy.get('#place_create').click()
        cy.get('.big-title').contains(fixture.place)
        cy.visit('/wiki/' + fixture.act6)
        cy.get('a').contains(fixture.place).should('not.have.class', 'new')
    })

    it('checks wiki links', () => {
        cy.visit('/wiki/' + fixture.title)
        cy.get('a').contains("Puppetmaster").should('have.class', 'new').click()
        cy.get('main a .icon-user-plus').click()
        cy.get('#npc_title').should('have.value', 'Project 2501')
    })

    it('rolls dice', () => {
        cy.visit('/wiki/' + fixture.protagonist, {
            onBeforeLoad(win) {
                win.localStorage.setItem('symfony/profiler/toolbar/displayState', 'none')
            }
        })

        cy.intercept('https://www.random.org/integers/*').as('getDice')
        cy.get('td').contains("Agilité").parent().find('button').click()
        cy.wait('@getDice')
        cy.get('.diceroller .roll-result > div').invoke('text').should('match', /\d{1,2}/)
        cy.wait(350)  // bcause of fake wait inside rollpool
        cy.screenshot('Motoko from GitS rolls Agility', {overwrite: true})
    })

    it('uploads a picture profile', () => {
        cy.visit('/wiki/' + fixture.protagonist)
        cy.get('.big-title .icon-user-circle').click()
        cy.get('#profile_pic_avatar').selectFile(fixture.profilepic)
        cy.get('#profile_pic_generate').click()
        cy.get('img[src^="/picture/get/token"').should('exist')
        cy.get('img[src^="/profile/unique/"]').click()
        cy.get('.notif .flash-success').contains('sent')
        // fetch profile pic
        cy.get(`img[src^="/profile/unique/"]`).then(img => {
            fetch(img[0].src).then(resp => {
                expect(resp.status).to.equal(200)
                expect(resp.headers.get('content-type')).to.equal('image/png')
            })
        })
    })

    it('sleeves in a different morph', () => {
        cy.visit('/wiki/' + fixture.protagonist)
        cy.get('.big-title .icon-sleeve').click()
        cy.get('section[x-html="detail"] h2').should('contain', fixture.morph)

        cy.intercept('/npc/morph/info*').as('morph')
        cy.get('#form_morph').select(fixture.newMorph)
        cy.wait('@morph')
        cy.get('section[x-html="detail"] h2').should('contain', fixture.newMorph)
    })
})