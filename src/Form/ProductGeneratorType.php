<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductGeneratorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productImage', FileType::class, [
                'label' => 'Slika proizvoda',
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Molimo otpremite validnu sliku (JPEG, PNG, JPG).'
                    ])
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'accept' => '.jpeg,.png,.jpg'
                ]
            ])
            ->add('productDescription', TextareaType::class, [
                'label' => 'Kratki opis proizvoda',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'placeholder' => 'Ukratko opišite proizvod (npr. crna kafa u beloj šolji, sportska patika, telefon...)',
                    'rows' => 3
                ]
            ])
            ->add('modelGender', ChoiceType::class, [
                'label' => 'Model / Pol',
                'required' => false,
                'choices' => [
                    'Muški' => 'muski',
                    'Ženski' => 'zenski',
                    'Dete' => 'dete',
                    'Samo proizvod (bez osoba)' => 'samo_proizvod'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('modelType', ChoiceType::class, [
                'label' => 'Broj i tip modela',
                'required' => false,
                'choices' => [
                    '1 osoba (koristi Model/Pol)' => '1_osoba',
                    '2 muškarca' => '2_muskarca',
                    '2 žene' => '2_zene',
                    'Muškarac i žena' => 'muskarac_zena',
                    'Muškarac i dete' => 'muskarac_dete',
                    'Žena i dete' => 'zena_dete',
                    '2 deteta' => '2_deteta',
                    'Grupa (3+ osoba)' => 'grupa'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('modelAge', TextType::class, [
                'label' => 'Godine',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'placeholder' => 'Unesite godine modela (npr. 25, 30-35, tinejdžer...)'
                ]
            ])
            ->add('ethnicity', ChoiceType::class, [
                'label' => 'Etnička pripadnost / Rasa',
                'required' => false,
                'choices' => [
                    'Belac/Belkinja' => 'belac',
                    'Crnac/Crnkinja' => 'crnac',
                    'Azijat/Azijatkinja' => 'azijat',
                    'Latino/Latina' => 'latino',
                    'Arap/Arapkinja' => 'arap',
                    'Indijac/Indijka' => 'indijac',
                    'Mešovita rasa' => 'mesovita',
                    'Skandinavac/Skandinavka' => 'skandinavac',
                    'Mediteranac/Mediteranka' => 'mediteranac',
                    'Sloven/Slovenka' => 'sloven',
                    'Bez preferencije' => 'bez_preferencije'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('clothingStyle', ChoiceType::class, [
                'label' => 'Stil odeće / garderobe',
                'required' => false,
                'choices' => [
                    'Elegantno odelo' => 'elegantno',
                    'Business casual' => 'business_casual',
                    'Sportska odeća' => 'sportska',
                    'Letnji stil (majica, šorts)' => 'letnji',
                    'Zimski stil (jakna, kapa)' => 'zimski',
                    'Plažni stil (kupaći, lagana odeća)' => 'plazni',
                    'Radna uniforma (građevinska)' => 'radna_gradjevinska',
                    'Radna uniforma (medicinska)' => 'radna_medicinska',
                    'Radna uniforma (ugostiteljstvo)' => 'radna_ugostiteljstvo',
                    'Minimalistički / neutralni outfit' => 'minimalisticki'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('sceneEnvironment', ChoiceType::class, [
                'label' => 'Scena / Okruženje',
                'required' => false,
                'choices' => [
                    'Dnevna soba' => 'dnevna_soba',
                    'Kuhinja' => 'kuhinja',
                    'Spavaća soba' => 'spavaca_soba',
                    'Kupatilo' => 'kupatilo',
                    'Radni prostor / kancelarija' => 'radni_prostor',
                    'Photo studio' => 'photo_studio',
                    'Minimalistički set' => 'minimalisticki_set',
                    'Restoran, kafić' => 'restoran_kafic',
                    'Prodavnica, shopping mall' => 'prodavnica',
                    'Sportski teren, teretana' => 'sportski_teren',
                    'Automobil' => 'automobil',
                    'Dvorište, balkon' => 'dvoriste_balkon',
                    'Krov, terasa' => 'krov_terasa',
                    'Park, šuma, livada' => 'park_suma',
                    'More, plaža, pesak' => 'more_plaza',
                    'Ulica, trg, urban setting' => 'ulica_trg',
                    'Parking, garaža' => 'parking_garaza',
                    'Gradilište, konstrukcija' => 'gradiliste',
                    'Zgrada, fasada' => 'zgrada_fasada',
                    'Bioskop, pozorište' => 'bioskop_pozoriste',
                    'Bolnica, klinika' => 'bolnica_klinika'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('activity', ChoiceType::class, [
                'label' => 'Aktivnost / Interakcija',
                'required' => false,
                'choices' => [
                    'Drži proizvod' => 'drzi_proizvod',
                    'Koristi proizvod' => 'koristi_proizvod',
                    'Pokazuje proizvod kameri' => 'pokazuje_proizvod',
                    'Nasmejan i angažovan' => 'nasmejan_angazovan',
                    'Sedi dok koristi proizvod' => 'sedi_koristi',
                    'Hoda dok koristi proizvod' => 'hoda_koristi',
                    'Opšta aktivnost, dinamična scena' => 'opsta_aktivnost'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('lighting', ChoiceType::class, [
                'label' => 'Osvetljenje / Atmosfera',
                'required' => false,
                'choices' => [
                    'Meko prirodno svetlo' => 'meko_prirodno',
                    'Sunčano' => 'suncano',
                    'Zlatni sat' => 'zlatni_sat',
                    'Zalazak sunca' => 'zalazak_sunca',
                    'Profesionalno osvetljenje' => 'profesionalno',
                    'Intimna atmosfera' => 'intimna_atmosfera'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('photoStyle', ChoiceType::class, [
                'label' => 'Stil fotografije',
                'required' => false,
                'choices' => [
                    'Ultra-realistično' => 'ultra_realisticno',
                    '4K detalji' => '4k_detalji',
                    'Zamućena pozadina' => 'zamucena_pozadina',
                    'Svetla i čista scena' => 'svetla_cista',
                    'Autentična, lifestyle feeling' => 'autenticna_lifestyle'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('perspective', ChoiceType::class, [
                'label' => 'Perspektiva / Fokus',
                'required' => false,
                'choices' => [
                    'Fokus na proizvod (close-up)' => 'fokus_proizvod',
                    'Osoba + proizvod u sceni (mid-shot)' => 'osoba_proizvod',
                    'Celo okruženje sa osobom i proizvodom (wide shot)' => 'celo_okruzenje'
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Generate ✨',
                'attr' => [
                    'class' => 'w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}