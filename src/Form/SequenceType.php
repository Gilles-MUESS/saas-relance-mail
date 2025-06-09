<?php

namespace App\Form;

use App\Entity\Sequence;
use App\Entity\Recipient;
use App\Entity\SequenceLabel;
use App\Entity\UserEmailAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
			->add( 'userEmailAccount', EntityType::class, [
				'class' => UserEmailAccount::class,
				'choice_label' => function (UserEmailAccount $account): string {
					return $account->getEmail();
				},
				'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
					return $er->createQueryBuilder( 'a' )
						->orderBy( 'a.email', 'ASC' );
				},
				'label' => "Adresse d'expédition",
				'placeholder' => 'Sélectionner une adresse',
				'required' => true,
				'multiple' => false,
				'expanded' => false,
				'attr' => [
					'class' => 'form-select',
				],
			] )
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => Sequence::STATUS_DRAFT,
                    'Activer maintenant' => Sequence::STATUS_ACTIVE,
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => Sequence::STATUS_DRAFT, // Valeur par défaut
            ])
		;
	}

	public function configureOptions( OptionsResolver $resolver ): void {
		$resolver->setDefaults( [
			'data_class' => Sequence::class,
			'user' => null,
		] );
	}
}
