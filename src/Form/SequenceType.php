<?php

namespace App\Form;

use App\Entity\Sequence;
use App\Entity\Recipient;
use App\Entity\SequenceLabel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SequenceType extends AbstractType {
	public function buildForm( FormBuilderInterface $builder, array $options ): void {
		$builder
			->add( 'messages', CollectionType::class, [ 
				'entry_type' => MessageType::class,
				'entry_options' => [ 
					'label' => false,
				],
				'allow_add' => true,
				'allow_delete' => true,
				'by_reference' => false,
			] )
			->add( 'label', EntityType::class, [ 
				'required' => false,
				'class' => SequenceLabel::class,
				'choice_label' => 'title',
				'label' => 'Catégorie',
				'placeholder' => 'Sélectionner une catégorie',
				'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
					return $er->createQueryBuilder( 'l' )
						->orderBy( 'l.title', 'ASC' );
				},
			] )
			->add( 'recipient', EntityType::class, [ 
				'class' => Recipient::class,
				'choice_label' => function (Recipient $recipient): string {
					return $recipient->getFirstName() . ' ' . $recipient->getLastName();
				},
				'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
					return $er->createQueryBuilder( 'r' )
						->orderBy( 'r.firstName', 'ASC' );
				},
				'label' => 'Destinataire(s)',
				'placeholder' => 'Sélectionner un destinataire',
				'required' => true,
				'multiple' => true,
				'expanded' => false,
				'attr' => [ 
					'class' => 'form-select select2-enable',
				],
			] )
		;
	}

	public function configureOptions( OptionsResolver $resolver ): void {
		$resolver->setDefaults( [ 
			'data_class' => Sequence::class,
			'user' => null,
		] );
	}
}
