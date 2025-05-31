<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Color;
use App\Entity\SequenceLabel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SequenceLabelType extends AbstractType {
	public function buildForm( FormBuilderInterface $builder, array $options ): void {
		$builder
			->add( 'title', TextType::class, [ 
				'label' => "Titre de la catégorie",
				'required' => true,
				'attr' => [ 
					'placeholder' => 'Titre de la catégorie',
					'class' => 'form-control',
				],
				'constraints' => [ 
					new NotBlank( [ 
						'message' => 'Le prénom est requis.',
					] ),
				],
			] )
			->add( 'color', EntityType::class, [ 
				'class' => Color::class,
				'choice_label' => 'name',
				'required' => false
			] )
		;
	}

	public function configureOptions( OptionsResolver $resolver ): void {
		$resolver->setDefaults( [ 
			'data_class' => SequenceLabel::class,
		] );
	}
}
